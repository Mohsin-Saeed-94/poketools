import csv
import math
import os
import re
import sys

from slugify import slugify

from inc import pokemon_text
from inc.yaml import yaml


def getFile() -> bytes:
    return open(sys.argv[1], 'rb').read()


def _getMoveNames(data: bytes, versiongroup: str) -> dict:
    if versiongroup == 'yellow':
        movenames = data[0x0BC000:0x0BC60E]
    else:
        movenames = data[0x0B0000:0x0B060E]

    moveid = 1
    moves = {}
    namebytes = bytearray()
    for namebyte in movenames:
        if namebyte != 0x50:
            namebytes.append(namebyte)
            continue

        name = namebytes.decode('pokemon_gen1')
        moves[moveid] = name

        namebytes = bytearray()
        moveid = moveid + 1

    return moves


def items():
    _shop_order = [
        'viridian-city/whole-area/mart',
        'pewter-city/whole-area/mart',
        'cerulean-city/whole-area/mart',
        'cerulean-city/whole-area/bike-shop',
        'vermilion-city/whole-area/mart',
        'lavender-town/whole-area/mart',
        'celadon-city/department-store/2f/trainers-market-left',
        'celadon-city/department-store/2f/trainers-market-right',
        'celadon-city/department-store/4f/wiseman-gifts',
        'celadon-city/department-store/5f/drugstore-left',
        'celadon-city/department-store/5f/drugstore-right',
        'fuchsia-city/whole-area/mart',
        None,
        'cinnabar-island/whole-area/mart',
        'saffron-city/whole-area/mart',
        'indigo-plateau/whole-area/mart'
    ]

    data = getFile()

    # What version is this?
    versiongroupmap = {
        'POKEMON RED': 'red-blue',
        'POKEMON BLUE': 'red-blue',
        'POKEMON YELLOW': 'yellow'
    }
    versiongroup = data[0x134:0x143].rstrip(b'\x00')
    versiongroup = versiongroupmap[versiongroup.decode('ascii')]

    # Get item names and IDs
    if versiongroup == 'yellow':
        itemnames = data[0x45B7:0x491E]
    else:
        itemnames = data[0x472B:0x4A91]
    iteminfo = {}
    itemid = 1
    for namedata in itemnames.split(b'\x50'):
        name = namedata.decode(encoding='pokemon_gen1', errors='ignore')
        iteminfo[itemid] = {
            'identifier': slugify(name),
            'name': name,
            'pocket': 'misc',
            'buy': None,
            'sell': None
        }
        itemid = itemid + 1

    # Add TM/HM to the item table
    for machine in range(1, 51):
        name = 'TM{:02}'.format(machine)
        itemid = 0xC8 + machine
        iteminfo[itemid] = {
            'identifier': slugify(name),
            'name': name,
            'pocket': 'misc',
            'buy': None,
            'sell': None
        }
    for machine in range(1, 6):
        name = 'HM{:02}'.format(machine)
        itemid = 0xC3 + machine
        iteminfo[itemid] = {
            'identifier': slugify(name),
            'name': name,
            'pocket': 'misc',
            'buy': None,
            'sell': None
        }

    # item prices
    if versiongroup == 'yellow':
        itemprices = data[0x4494:0x45B6]
    else:
        itemprices = data[0x4608:0x472A]
    pricebytes = bytearray()
    itemid = 1
    for byte in itemprices:
        # The item prices are stored as binary-coded decimal in three bytes.
        pricebytes.append(byte)
        if len(pricebytes) < 3:
            continue

        price = int(pricebytes.hex())
        # Special case for the bicycle
        if iteminfo[itemid]['identifier'] == 'bicycle':
            price = 1000000
        if price > 0:
            iteminfo[itemid]['buy'] = price
            iteminfo[itemid]['sell'] = math.floor(price / 2)
        else:
            del iteminfo[itemid]['buy']
            del iteminfo[itemid]['sell']

        itemid = itemid + 1
        pricebytes = bytearray()

    # shops
    if versiongroup == 'yellow':
        shops = data[0x233B:0x23D0]
    else:
        shops = data[0x2442:0x24D6]
    shops = shops.split(b'\xFF\xFE')
    shopinfo = []
    shopid = 0
    for shopdata in shops:
        if _shop_order[shopid] is None:
            # This will skip the dummy shop in the game data.
            continue

        shopdata = shopdata.lstrip(b'\xFE').rstrip(b'\xFF')
        shoplocationparts = _shop_order[shopid].split('/')
        shoplocation = shoplocationparts.pop(0)
        shopidentifier = shoplocationparts.pop(-1)
        shoparea = '/'.join(shoplocationparts)
        for itemid in shopdata[1:]:
            shopinfo.append({
                'version_group': versiongroup,
                'location': shoplocation,
                'area': shoparea,
                'shop': shopidentifier,
                'item': iteminfo[itemid]['identifier'],
                'buy': iteminfo[itemid]['buy']
            })
        shopid = shopid + 1

    # Machine descriptions
    movenames = _getMoveNames(data, versiongroup)
    if versiongroup == 'yellow':
        machinemoves = data[0x01232D:0x012365]
    else:
        machinemoves = data[0x013773:0x0137AA]
    re_ismachine = re.compile(r'^(?P<type>tm|hm)(?P<number>\d{2})$', flags=re.IGNORECASE)
    for item in iteminfo.values():
        match = re_ismachine.match(item['identifier'])
        if not match:
            continue
        searchnumber = int(match.group('number'))
        if match.group('type') == 'hm':
            searchnumber = searchnumber + 50
        moveid = int(machinemoves[searchnumber - 1])
        movename = movenames[moveid]
        moveidentifier = slugify(movename)
        short_description = 'Teaches []{{move:{identifier}}} to a compatible Pokèmon.'.format(identifier=moveidentifier)
        description = r'''
Teaches []{{move:{move}}} to a compatible Pokèmon.

{{{{App\Controller\ItemController::tmPokemon({{"itemSlug": "{item}"}})}}}}
        '''.format(move=moveidentifier, item=item['identifier']).strip()
        item['short_description'] = short_description
        item['description'] = description

    # Write output
    os.makedirs('item', exist_ok=True)
    # Copy these values from later data
    pulldownkeys = [
        'category',
        'flags',
        'short_description',
        'description'
    ]
    for item in iteminfo.values():
        identifier = item['identifier']
        del item['identifier']

        if not identifier:
            # Skip dummy items
            continue

        # Read the existing file to add to it.
        filename = 'item/{name}.yaml'.format(name=identifier)
        if os.path.isfile(filename):
            outfile = open(filename, 'r')
            existingdata = yaml.load(outfile)
            outfile.close()
        else:
            existingdata = {}

        # Add in values from later version (pull-down)
        for key in pulldownkeys:
            if key in item:
                # Don't overwrite existing data
                continue
            for laterversiondata in existingdata.values():
                if key in laterversiondata:
                    item[key] = laterversiondata[key]
                    break

        # This odd dance will put new data at the top of the file.
        outdata = {versiongroup: {}}
        outdata.update(existingdata)
        outdata[versiongroup] = item

        outfile = open(filename, 'w')
        yaml.dump(outdata, outfile)
        outfile.close()
    if os.path.isfile('shop_item.csv'):
        with open('shop_item.csv', 'r') as outfile:
            reader = csv.DictReader(outfile)
            for row in reader:
                shopinfo.insert(0, row)
            outfile.close()
    with open('shop_item.csv', 'w') as outfile:
        writer = csv.DictWriter(outfile, fieldnames=shopinfo[0].keys())
        writer.writeheader()
        writer.writerows(shopinfo)
        outfile.close()


if __name__ == '__main__':
    pokemon_text.register()

    exit(items())
