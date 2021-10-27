import json
import time

from urllib.request import urlopen
from bs4 import BeautifulSoup as bs

base_url = 'https://www.gesetze-im-internet.de/Teilliste_{}.html'

data = {}

for category in 'ABCDEFGHIJKLMNOPQRSTUVWYZ123456789':
    # Fetch overview website
    client = urlopen(base_url.format(category))
    html = client.read()
    client.close()

    # Parse its HTML
    html = bs(html, 'html.parser')

    # Select relevant `p` tags
    links = html.select('#paddingLR12')[0].select('p')

    # Iterate over links ..
    for link in links:
        # .. extracting data
        law = link.a.text[1:-1]
        slug = link.a['href'][2:-11]
        title = link.a.abbr['title']

        # .. reporting current law
        print('Storing {} ..'.format(law))

        # .. storing its information
        data[law.lower()] = {
            'law': law,
            'slug': slug,
            'title': title,
        }

    # Wait for it ..
    time.sleep(2)

# Write data to JSON file
with open('data.json', 'w') as file:
    json.dump(data, file, ensure_ascii=False, indent=4)
