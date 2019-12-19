import csv
import math
import os
import re
import sys

from ruamel.yaml import YAML
from slugify import slugify

import pokemon_text


def getFile() -> bytes:
    return open(sys.argv[1], 'rb').read()


def _getMoveNames(data: bytes, versiongroup: str) -> dict:
    if versiongroup == 'crystal':
        movenames = data[0x1C9F29:0x1CA895]
    else:
        movenames = data[0x1B1574:0x1B1EE1]

    moveid = 1
    moves = {}
    namebytes = bytearray()
    for namebyte in movenames:
        if namebyte != 0x50:
            namebytes.append(namebyte)
            continue

        name = namebytes.decode('pokemon_gen2')
        moves[moveid] = name

        namebytes = bytearray()
        moveid = moveid + 1

    return moves


def items():
    _shop_order = [
        'cherrygrove-city/whole-area/mart-no-dex',
        'cherrygrove-city/whole-area/mart',
        'violet-city/whole-area/mart',
        'azalea-town/whole-area/mart',
        'cianwood-city/whole-area/mart',
        'goldenrod-city/department-store/2f/trainers-market-left',
        'goldenrod-city/department-store/2f/trainers-market-right',
        'goldenrod-city/department-store/3f/battle-collection',
        'goldenrod-city/department-store/4f/medicine-box',
        'goldenrod-city/department-store/5f/tm-corner-1',
        'goldenrod-city/department-store/5f/tm-corner-2',
        'goldenrod-city/department-store/5f/tm-corner-3',
        'goldenrod-city/department-store/5f/tm-corner-4',
        'olivine-city/whole-area/mart',
        'ecruteak-city/whole-area/mart',
        'mahogany-town/whole-area/shop',
        'mahogany-town/whole-area/shop-no-rockets',
        'blackthorn-city/whole-area/mart',
        'viridian-city/whole-area/mart',
        'pewter-city/whole-area/mart',
        'cerulean-city/whole-area/mart',
        'lavender-town/whole-area/mart',
        'vermilion-city/whole-area/mart',
        'celadon-city/department-store/2f/trainers-market-upper',
        'celadon-city/department-store/2f/trainers-market-lower',
        'celadon-city/department-store/3f/tm-shop',
        'celadon-city/department-store/4f/wiseman-gifts',
        'celadon-city/department-store/5f/drugstore-left',
        'celadon-city/department-store/5f/drugstore-right',
        'fuchsia-city/whole-area/mart',
        'saffron-city/whole-area/mart',
        'mt-moon/mt-moon-square/shop',
        'indigo-plateau/whole-area/mart',
        'goldenrod-underground/whole-area/herb-shop'
    ]

    data = getFile()

    # What version is this?
    versiongroupmap = {
        'POKEMON_GLDAAUE': 'gold-silver',
        'POKEMON_SLVAAXE': 'red-blue',
        'PM_CRYSTAL\x00BYTE': 'crystal'
    }
    versiongroup = data[0x134:0x143]
    versiongroup = versiongroupmap[versiongroup.decode('ascii')]

    # Get item names and IDs
    if versiongroup == 'crystal':
        itemnames = data[0x1C8000:]
    else:
        itemnames = data[0x1B0000:]
    iteminfo = {}
    itemid = 1
    namebytes = bytearray()
    for namebyte in itemnames:
        if itemid > 256:
            break
        if namebyte != 0x50:
            namebytes.append(namebyte)
            continue

        name = namebytes.decode(encoding='pokemon_gen2', errors='ignore')
        iteminfo[itemid] = {
            'identifier': slugify(name),
            'name': name,
            'pocket': None,
            'buy': None,
            'sell': None,
            'flavor_text': None
        }
        itemid = itemid + 1
        namebytes = bytearray()

    # item prices/pockets
    if versiongroup == 'crystal':
        itemattrs = data[0x67C1:0x6EC0]
    else:
        itemattrs = data[0x68A0:0x6F9F]
    attrbytes = bytearray()
    itemid = 1
    pocketmap = {
        0x01: 'misc',
        0x02: 'key',
        0x03: 'pokeballs',
        0x04: 'machines'
    }
    for byte in itemattrs:
        # The item attributes are stored in a seven byte sequence.  The first
        # two bytes are an unsigned integer with the price.
        attrbytes.append(byte)
        if len(attrbytes) < 7:
            continue

        price = attrbytes[0] + (attrbytes[1] << 8)
        if price > 0:
            iteminfo[itemid]['buy'] = price
            iteminfo[itemid]['sell'] = math.floor(price / 2)
        else:
            del iteminfo[itemid]['buy']
            del iteminfo[itemid]['sell']

        iteminfo[itemid]['pocket'] = pocketmap[attrbytes[5]]

        itemid = itemid + 1
        attrbytes = bytearray()

    # shops
    if versiongroup == 'crystal':
        shoppointers = data[0x160A9:0x160ED]
    else:
        shoppointers = data[0x162FE:0x16342]
    shopinfo = []
    shopid = 0
    shoppointerbytes = bytearray()
    for shoppointerbyte in shoppointers:
        shoppointerbytes.append(shoppointerbyte)
        if len(shoppointerbytes) < 2:
            continue
        shoppointer = shoppointerbytes[0] + (shoppointerbytes[1] << 8) + 0x10000

        shopdata = data[shoppointer:]
        shopitemcount = int(shopdata[0])
        shoplocationparts = _shop_order[shopid].split('/')
        shoplocation = shoplocationparts.pop(0)
        shopidentifier = shoplocationparts.pop(-1)
        shoparea = '/'.join(shoplocationparts)
        for itemid in shopdata[1:shopitemcount]:
            shopinfo.append({
                'version_group': versiongroup,
                'location': shoplocation,
                'area': shoparea,
                'shop': shopidentifier,
                'item': iteminfo[itemid]['identifier'],
                'buy': iteminfo[itemid]['buy']
            })

        shopid = shopid + 1
        shoppointerbytes = bytearray()

    # Flavor text
    if versiongroup == 'crystal':
        flavordata = data[0x1C8B85:]
    else:
        flavordata = data[0x1B8200:]
    itemid = 1
    flavorbytes = bytearray()
    for flavorbyte in flavordata:
        if itemid > 256:
            break
        if flavorbyte != 0x50:
            # String terminator
            flavorbytes.append(flavorbyte)
            continue

        flavor = flavorbytes.decode('pokemon_gen2')
        iteminfo[itemid]['flavor_text'] = flavor

        itemid = itemid + 1
        flavorbytes = bytearray()

    # Machine descriptions
    movenames = _getMoveNames(data, versiongroup)
    if versiongroup == 'crystal':
        machinemoves = data[0x01167A:0x0116B3]
    else:
        machinemoves = data[0x011A66:0x011A9F]
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
        del item['flavor_text']

    # Write output
    os.makedirs('item', exist_ok=True)
    yaml = YAML()
    yaml.default_flow_style = False
    yaml.indent(mapping=2, sequence=4, offset=2)
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

        if identifier == 'teru-sama' or not identifier:
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
