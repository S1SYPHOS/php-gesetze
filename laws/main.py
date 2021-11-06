import os
import re
import glob
import json
import time

from bs4 import BeautifulSoup as bs
from requests import get as get_url


path = os.path.dirname(__file__)

# Determine data files
data_files = [path + '/{}.json'.format(char) for char in 'ABCDEFGHIJKLMNOPQRSTUVWYZ123456789']

# Iterate over files
for data_file in data_files:
    # If data file already exists ..
    if os.path.exists(data_file):
        # .. proceed with next one
        continue

    # Create data array
    data = {}

    # Fetch overview page for category letter
    html = get_url('https://www.gesetze-im-internet.de/Teilliste_{}.html'.format(os.path.basename(data_file))).text

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
        html = get_url('https://www.gesetze-im-internet.de/{}/index.html'.format(slug)).text

        # Iterate over `a` tags ..
        for heading in bs(law_html, 'html.parser').select('#paddingLR12')[0].select('td'):
            # (1) .. skipping headings without `a` tag child
            if not heading.a:
                continue

            # (2) .. skipping headings without `href` attribute in `a` tag child
            if not heading.a.get('href'):
                continue

            # # Determine section identifier
            match = re.match(r'(?:§+|Art|Artikel)\.?\s*(\d+(?:\w\b)?)', heading.text, re.IGNORECASE)

            # # If section identifier was found ..
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

    # Waaaait for it ..
    time.sleep(3)

    # Write data to JSON file
    with open(data_file, 'w') as file:
        json.dump(data, file, ensure_ascii=False)

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
with open(path + '/data.json', 'w') as file:
    json.dump(data, file, ensure_ascii=False)

# Iterate over data files ..
for data_file in data_files:
    # .. deleting each one of them
    os.remove(data_file)
