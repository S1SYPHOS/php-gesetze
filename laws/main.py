import os
import re
import glob
import json
import time

from bs4 import BeautifulSoup as bs
from requests import get as get_url


path = os.path.dirname(__file__)


def gesetze() -> None:
    # Define identifier
    identifier = 'gesetze'

    # Create data directory
    # (1) Define its path
    base = '{}/{}'.format(path, identifier)

    # (2) Create (if necessary)
    create_path(base)

    # Determine data files
    data_files = ['{}/{}.json'.format(base, char) for char in 'ABCDEFGHIJKLMNOPQRSTUVWYZ123456789']

    # Iterate over files
    for data_file in data_files:
        # If data file already exists ..
        if os.path.exists(data_file):
            # .. proceed with next one
            continue

        # Create data array
        data = {}

        # Fetch overview page for category letter
        html = get_url('https://www.gesetze-im-internet.de/Teilliste_{}.html'.format(os.path.basename(data_file)[:1])).text

        # Parse their HTML & iterate over `p` tags ..
        for link in bs(html, 'html.parser').select('#paddingLR12')[0].select('p'):
            # .. extracting data for each law
            law = link.a.text[1:-1]
            slug = link.a['href'][2:-11]
            title = link.a.abbr['title']

            # .. reporting current law
            print('Storing {} ..'.format(law))

            # .. collecting its information
            node = {
                'law': law,
                'slug': slug,
                'title': title,
                'headings': {},
            }

            # Fetch index page for each law
            law_html = get_url('https://www.gesetze-im-internet.de/{}/index.html'.format(slug)).text

            # Iterate over `a` tags ..
            for heading in bs(law_html, 'html.parser').select('#paddingLR12')[0].select('td'):
                # (1) .. skipping headings without `a` tag child
                if not heading.a:
                    continue

                # (2) .. skipping headings without `href` attribute in `a` tag child
                if not heading.a.get('href'):
                    continue

                # Determine section identifier
                match = re.match(r'(?:§+|Art|Artikel)\.?\s*(\d+(?:\w\b)?)', heading.text, re.IGNORECASE)

                # If section identifier was found ..
                if match:
                    # .. store identifier as key and heading as value
                    node['headings'][match.group(1)] = heading.text.strip()

                # .. otherwise ..
                else:
                    # .. store heading as both key and value
                    node['headings'][heading.text.strip()] = heading.text.strip()

            # Store data record
            data[law.lower()] = node

            # Wait for it ..
            time.sleep(2)

        # Write data to JSON file
        with open(data_file, 'w') as file:
            json.dump(data, file, ensure_ascii=False)

    merge_data(identifier, data_files)


def dejure() -> None:
    # Define identifier
    identifier = 'dejure'

    # Create data directory
    # (1) Define its path
    base = '{}/{}'.format(path, identifier)

    # (2) Create (if necessary)
    create_path(base)

    # Fetch overview page
    html = get_url('https://dejure.org').text

    # Determine data files
    data_files = ['{}/{}.json'.format(base, char) for char in 'ABDEFGHIJKLMNOPRSTUVWZ']

    # Iterate over files
    for data_file in data_files:
        # If data file already exists ..
        if os.path.exists(data_file):
            # .. proceed with next one
            continue

        # Create data array
        data = {}

        # Parse their HTML & iterate over their sibling's `li` tags ..
        for link in bs(html, 'html.parser').find('a', attrs={'name': os.path.basename(data_file)[:1]}).find_next_sibling('ul').select('li'):
            # .. extracting data for each law
            law = link.a.text
            slug = link.a['href'][9:]
            title = link.text.replace(link.a.text, '').strip('( )')

            # .. reporting current law
            print('Storing {} ..'.format(law))

            # .. collecting its information
            node = {
                'law': law,
                'slug': slug,
                'title': title,
                'headings': {},
            }

            # Fetch index page for each law
            law_html = get_url('https://dejure.org/gesetze/{}'.format(slug)).text

            # Iterate over `p` tags ..
            for heading in bs(law_html, 'html.parser').find_all('p', attrs={'class': 'clearfix'}):
                # (1) .. skipping headings without `a` tag child
                if not heading.a:
                    continue

                # (2) .. skipping headings without `href` attribute in `a` tag child
                if not heading.a.get('href'):
                    continue

                # Determine section identifier
                match = re.match(r'(?:§+|Art|Artikel)\.?\s*(\d+(?:\w\b)?)', heading.text, re.IGNORECASE)

                # If section identifier was found ..
                if match:
                    # .. store identifier as key and heading as value
                    node['headings'][match.group(1)] = heading.text.strip().replace('§  ', '§ ')

                # .. otherwise ..
                else:
                    # .. store heading as both key and value
                    node['headings'][heading.text.strip()] = heading.text.strip()

            # Store data record
            data[law.lower()] = node

            # Wait for it ..
            time.sleep(2)

        # Waaaait for it ..
        time.sleep(3)

        # Write data to JSON file
        with open(data_file, 'w') as file:
            json.dump(data, file, ensure_ascii=False)

    merge_data(identifier, data_files)


def lexparency() -> None:
    # Define identifier
    identifier = 'lexparency'

    # Create data directory
    # (1) Define its path
    base = '{}/{}'.format(path, identifier)

    # (2) Create (if necessary)
    create_path(base)

    # Fetch overview page
    html = get_url('https://lexparency.de').text

    # Create data array
    data = {}

    # Parse their HTML & iterate over `a` tags ..
    for link in bs(html, 'html.parser').select('#featured-acts')[0].select('a'):
        # .. extracting data for each law
        law = link.text.strip()

        # Determine abbreviation
        match = re.match(r'.*\((.*)\)$', law)

        # If abbreviation was found ..
        if match:
            # .. store it as shorthand for current law
            law = match.group(1)

        slug = link['href'][4:]

        # .. reporting current law
        print('Storing {} ..'.format(law))

        # .. collecting its information
        node = {
            'law': law,
            'slug': slug,
            'title': '',
            'headings': {},
        }

        # Fetch index page for each law
        law_html = get_url('https://lexparency.de/eu/{}'.format(slug)).text

        # Get title
        # (1) Create empty list
        title = []

        # (2) Convert first character of second entry (= 'title essence') to lowercase
        for i, string in enumerate(list(bs(law_html, 'html.parser').select('h1')[0].stripped_strings)):
            if i == 1:
                string = string[:1].lower() + string[1:]

            title.append(string)

        # (3) Create title from strings
        node['title'] = ' '.join(title).strip()

        # Iterate over `li` tags ..
        for heading in bs(law_html, 'html.parser').select('#toccordion')[0].find_all('li', attrs={'class': 'toc-leaf'}):
            # (1) .. skipping headings without `a` tag child
            if not heading.a:
                continue

            # (2) .. skipping headings without `href` attribute in `a` tag child
            if not heading.a.get('href'):
                continue

            string = heading.text.replace('—', '-')

            # Determine section identifier
            match = re.match(r'(?:§+|Art|Artikel)\.?\s*(\d+(?:\w\b)?)', string, re.IGNORECASE)

            # If section identifier was found ..
            if match:
                # .. store identifier as key and heading as value
                node['headings'][match.group(1)] = string.replace('§  ', '§ ')

            # .. otherwise ..
            else:
                # .. store heading as both key and value
                node['headings'][string] = string

        # Store data record
        data[law.lower()] = node

        # Wait for it ..
        time.sleep(2)

    # Write complete dataset to JSON file
    with open('{}/{}.json'.format(path, identifier), 'w') as file:
        json.dump(data, file, ensure_ascii=False)


def merge_data(identifier: str, data_files: list) -> None:
    # Create data array
    data = {}

    # Iterate over data files ..
    for data_file in data_files:
        # It happens, alright ..
        if not os.path.exists(data_file):
            continue

        # .. deleting each one of them
        with open(data_file, 'r') as file:
            data.update(json.load(file))

    # Write complete dataset to JSON file
    with open('{}/{}.json'.format(path, identifier), 'w') as file:
        json.dump(data, file, ensure_ascii=False)

    # Iterate over data files ..
    for data_file in data_files:
        # .. deleting each one of them
        os.remove(data_file)


def create_path(path):
    # If path does not exist ..
    if not os.path.exists(path):
        # .. attempt to ..
        try:
            # .. create it
            os.makedirs(path)

        # Guard against race condition
        except OSError:
            pass


if __name__ == '__main__':
    print('Current database: "gesetze-im-internet.de" ..')
    gesetze()

    print('Current database: "dejure.org" ..')
    dejure()

    print('Current database: "lexparency.de" ..')
    lexparency()
