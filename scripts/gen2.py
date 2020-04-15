# Rip Gen 2 pokemon data

import argparse
import csv
import math
import os
import re

import progressbar
import slugify

from inc import gb, pokemon_text
from inc.yaml import yaml

pokemon_text.register()

_type_map = {
    0x00: 'normal',
    0x01: 'fighting',
    0x02: 'flying',
    0x03: 'poison',
    0x04: 'ground',
    0x05: 'rock',
    0x06: 'bird',
    0x07: 'bug',
    0x08: 'ghost',
    0x09: 'steel',
    0x13: 'unknown',
    0x14: 'fire',
    0x15: 'water',
    0x16: 'grass',
    0x17: 'electric',
    0x18: 'psychic',
    0x19: 'ice',
    0x1A: 'dragon',
    0x1B: 'dark',
}

# Get config
argparser = argparse.ArgumentParser(description='Load Gen 2 data.')
argparser.add_argument('--rom', type=argparse.FileType('rb'), required=True, help='ROM File path')
argparser.add_argument('--out-pokemon', type=str, required=True, help='Pokemon YAML file dir')
argparser.add_argument('--out-pokemon_moves', type=str, required=True, help='Pokemon Move CSV file')
argparser.add_argument('--out-moves', type=str, required=True, help='Move YAML file dir')
argparser.add_argument('--out-items', type=str, required=True, help='Item YAML file dir')
argparser.add_argument('--out-shops', type=str, required=True, help='Shop Data CSV file')
argparser.add_argument('--out-encounters', type=str, required=True, help='Encounter CSV file')
argparser.add_argument('--write-pokemon', action='store_true', help='Write Pokemon data')
argparser.add_argument('--write-pokemon_moves', action='store_true', help='Write Pokemon move data')
argparser.add_argument('--write-moves', action='store_true', help='Write Move data')
argparser.add_argument('--write-items', action='store_true', help='Write Item data')
argparser.add_argument('--write-shops', action='store_true', help='Write Shop data')
argparser.add_argument('--write-encounters', action='store_true', help='Write Shop data')
args = argparser.parse_args()

rom = args.rom.read()

# What version is this?
versionmap = {
    'POKEMON_GLDAAUE': 'gold',
    'POKEMON_SLVAAXE': 'silver',
    'PM_CRYSTAL\x00BYTE': 'crystal'
}
versiongroupmap = {
    'gold': 'gold-silver',
    'silver': 'gold-silver',
    'crystal': 'crystal'
}

version = rom[0x134:0x143]
version = version.decode('ascii')
version = versionmap[version]
version_group = versiongroupmap[version]
print('Using version group {version_group}'.format(version_group=version_group))
print('Using version {version}'.format(version=version))

# Slug maps keyed by internal id
move_slugs = {}
move_name_changes = {
    'ancientpower': 'ancient-power',
    'bubblebeam': 'bubble-beam',
    'doubleslap': 'double-slap',
    'conversion2': 'conversion-2',
    'dragonbreath': 'dragon-breath',
    'dynamicpunch': 'dynamic-punch',
    'extremespeed': 'extreme-speed',
    'faint-attack': 'feint-attack',
    'featherdance': 'feather-dance',
    'grasswhistle': 'grass-whistle',
    'hi-jump-kick': 'high-jump-kick',
    'poisonpowder': 'poison-powder',
    'selfdestruct': 'self-destruct',
    'smellingsalt': 'smelling-salts',
    'softboiled': 'soft-boiled',
    'solarbeam': 'solar-beam',
    'sonicboom': 'sonic-boom',
    'thunderpunch': 'thunder-punch',
    'thundershock': 'thunder-shock',
    'vicegrip': 'vice-grip',
}


def get_moves():
    print('Dumping moves')
    # Names
    if version_group == 'gold-silver':
        move_names_offset = 0x1B1574
    else:
        move_names_offset = 0x1C9F29
    move_names = {}
    name = bytearray()
    move_id = 1
    print('Extracting move names')
    for char in rom[move_names_offset:]:
        if char == 0x50:
            decoded_name = name.decode('pokemon_gen2')
            move_slug = slugify.slugify(decoded_name)
            move_names[move_id] = decoded_name
            move_slugs[move_id] = move_slug
            name = bytearray()
            move_id += 1
        elif char == 0x00:
            # End of name table
            break
        else:
            name += char.to_bytes(1, byteorder='little')

    # Data
    out = {}
    move_data_length = 7
    if version_group == 'gold-silver':
        move_data_offset = 0x041AFE
    else:
        move_data_offset = 0x041AFB

    # These fields come from the way the effect is scripted and can't
    # be directly ripped from the game
    pullup_keys = [
        'crit_rate_bonus',
        'drain',
        'flinch_chance',
        'ailment',
        'ailment_chance',
        'recoil',
        'healing',
        'flags',
        'categories',
        'hits',
        'turns',
        'stat_changes',
        'stat_change_chance',
        'priority',
        'target',
    ]
    # move slug => effect id
    fixed_damage_moves = {
        'sonicboom': 131,
        'dragon-rage': 42,
    }
    print('Extracting move data')
    for move_index, move_slug in move_slugs.items():
        start = move_data_offset + ((move_index - 1) * move_data_length)
        end = start + move_data_length
        entry = rom[start:end]

        effect_id = entry[0x01]
        out[move_slug] = {
            version_group: {
                'name': move_names[move_index],
                'power': entry[0x02],
                'type': _type_map[entry[0x03]],
                'accuracy': round(entry[0x04] / 255 * 100),
                'pp': entry[0x05],
                'effect': effect_id + 1,
            }
        }
        if entry[0x06] > 0:
            out[move_slug][version_group]['effect_chance'] = round(entry[0x06] / 255 * 100)
        if out[move_slug][version_group]['power'] == 0:
            del out[move_slug][version_group]['power']
        if move_slug in fixed_damage_moves:
            # The game uses the same effect id for different damage amounts
            out[move_slug][version_group]['effect'] = fixed_damage_moves[move_slug]

        # Some extra info comes from the existing data
        if move_slug in move_name_changes:
            yaml_path = os.path.join(args.out_moves, '{move}.yaml'.format(move=move_name_changes[move_slug]))
        else:
            yaml_path = os.path.join(args.out_moves, '{move}.yaml'.format(move=move_slug))
        with open(yaml_path, 'r') as move_yaml:
            old_move_data = yaml.load(move_yaml.read())
        if version_group not in old_move_data:
            # If the name has changed, try the original name, as it may have been moved already.
            if move_slug in move_name_changes:
                yaml_path = os.path.join(args.out_moves, '{move}.yaml'.format(move=move_slug))
                with open(yaml_path, 'r') as move_yaml:
                    old_move_data = yaml.load(move_yaml.read())
        for key in pullup_keys:
            if key in old_move_data[version_group]:
                out[move_slug][version_group][key] = old_move_data[version_group][key]

        # Flavor text
        if version_group == 'gold-silver':
            flavor_pointer_offset = 0x1B4000
            flavor_bank = 0x6D
        else:
            flavor_pointer_offset = 0x02CB52
            flavor_bank = 0x0B
        flavor_start = flavor_pointer_offset + ((move_index - 1) * 2)
        flavor_pointer = rom[flavor_start:flavor_start + 2]
        flavor_cursor = gb.address_from_pointer(flavor_pointer, flavor_bank)
        flavor_text = bytearray()
        while rom[flavor_cursor] != 0x50:
            flavor_text += rom[flavor_cursor].to_bytes(1, byteorder='little')
            flavor_cursor += 1
        flavor_text = flavor_text.decode('pokemon_gen2')
        out[move_slug][version_group]['flavor_text'] = flavor_text

    return out


out_moves = get_moves()

# Slug maps keyed by internal id
item_slugs = {}
tm_moves = {}
item_name_changes = {
    'thunderstone': 'thunder-stone',
}


def get_items():
    print('Dumping items')
    shop_order = [
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
    slug_overrides = {
        'king-s-rock': 'kings-rock',
        's-s-ticket': 'ss-ticket',
    }
    # Get item names and IDs
    if version_group == 'crystal':
        itemnames = rom[0x1C8000:]
    else:
        itemnames = rom[0x1B0000:]
    iteminfo = {}
    itemid = 1
    namebytes = bytearray()
    print('Extracting item names')
    for namebyte in itemnames:
        if itemid > 256:
            break
        if namebyte != 0x50:
            namebytes.append(namebyte)
            continue

        name = namebytes.decode(encoding='pokemon_gen2', errors='ignore')
        slug = slugify.slugify(name)
        if slug in slug_overrides:
            slug = slug_overrides[slug]
        iteminfo[itemid] = {
            'identifier': slug,
            'name': name,
            'pocket': None,
            'buy': None,
            'sell': None,
            'flavor_text': None
        }
        item_slugs[itemid] = slug
        itemid = itemid + 1
        namebytes = bytearray()

    # item prices
    if version == 'crystal':
        itemattrs = rom[0x67C1:0x6EC0]
    elif version == 'gold':
        itemattrs = rom[0x68A0:0x6F9F]
    else:
        itemattrs = rom[0x6866:0x6F65]
    attrbytes = bytearray()
    itemid = 1
    pocketmap = {
        0x01: 'misc',
        0x02: 'key',
        0x03: 'pokeballs',
        0x04: 'machines'
    }
    print('Extracting item attributes')
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
    if version_group == 'crystal':
        shoppointers = rom[0x160A9:0x160ED]
    else:
        shoppointers = rom[0x162FE:0x16342]
    out_shops = []
    shopid = 0
    shoppointerbytes = bytearray()
    print('Dumping shop data')
    for shoppointerbyte in shoppointers:
        shoppointerbytes.append(shoppointerbyte)
        if len(shoppointerbytes) < 2:
            continue
        shoppointer = shoppointerbytes[0] + (shoppointerbytes[1] << 8) + 0x10000

        shopdata = rom[shoppointer:]
        shopitemcount = int(shopdata[0])
        shoplocationparts = shop_order[shopid].split('/')
        shoplocation = shoplocationparts.pop(0)
        shopidentifier = shoplocationparts.pop(-1)
        shoparea = '/'.join(shoplocationparts)
        for itemid in shopdata[1:shopitemcount]:
            out_shops.append({
                'version_group': version_group,
                'location': shoplocation,
                'area': shoparea,
                'shop': shopidentifier,
                'item': iteminfo[itemid]['identifier'],
                'buy': iteminfo[itemid]['buy']
            })

        shopid = shopid + 1
        shoppointerbytes = bytearray()

    # Flavor text
    if version_group == 'crystal':
        flavordata = rom[0x1C8B85:]
    else:
        flavordata = rom[0x1B8200:]
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
    if version_group == 'crystal':
        machinemoves = rom[0x01167A:0x0116B3]
    else:
        machinemoves = rom[0x011A66:0x011A9F]
    re_ismachine = re.compile(r'^(?P<type>tm|hm)(?P<number>\d{2})$', flags=re.IGNORECASE)
    for item in iteminfo.values():
        match = re_ismachine.match(item['identifier'])
        if not match:
            continue
        # Is a TM/HM
        del item['flavor_text']
        searchnumber = int(match.group('number'))
        if match.group('type') == 'hm':
            searchnumber = searchnumber + 50
        move_id = int(machinemoves[searchnumber - 1])
        tm_moves[item['identifier']] = move_slugs[move_id]
        short_description = 'Teaches []{{move:{move}}} to a compatible Pokémon.'.format(move=move_slugs[move_id])
        description = '\n\n'.join([
            r'Teaches []{{move:{move}}} to a compatible Pokémon.',
            r'{{{{App\Controller\ItemController::tmPokemon({{"itemSlug": "{item}"}})}}}}'
        ]).format(move=move_slugs[move_id], item=item['identifier']).strip()
        item['short_description'] = short_description
        item['description'] = description

    # Put it all together
    out_items = {}
    # Copy these values from later data
    pulldownkeys = [
        'category',
        'flags',
        'short_description',
        'description'
    ]
    print('Using existing data for meta info')
    for item in iteminfo.values():
        identifier = item['identifier']
        del item['identifier']

        if identifier == 'teru-sama' or not identifier:
            # Skip dummy items
            continue

        # Read the existing file to add to it.
        filename = os.path.join(args.out_items, '{slug}.yaml'.format(slug=identifier))
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
            if version_group in existingdata and key in existingdata[version_group]:
                item[key] = existingdata[version_group][key]

        out_items[identifier] = {version_group: item}

    return out_items, out_shops


out_items, out_shops = get_items()

# Slug maps keyed by internal id
pokemon_slugs = {}


def get_pokemon():
    print('Dumping Pokemon')
    out_pokemon = {}
    out_pokemon_moves = []

    # Get names
    print('Extracting names')
    slug_overrides = {
        'farfetch-d': 'farfetchd',
    }
    if version_group == 'gold-silver':
        names_offset = 0x1B0B74
    else:
        names_offset = 0x053384
    name_length = 10
    for species_index in range(1, 252):
        start = names_offset + ((species_index - 1) * name_length)
        end = start + name_length
        entry = rom[start:end]
        name = entry.decode('pokemon_gen2').strip()
        slug = slugify.slugify(name.replace('♀', '-f').replace('♂', '-m'))
        if slug in slug_overrides:
            slug = slug_overrides[slug]
        pokemon_slugs[species_index] = slug
        out_pokemon[slug] = {
            version_group: {
                'name': name,
                'position': species_index - 1,
                'numbers': {'national': species_index}
            }
        }

    # Pokedex order
    print('Extracting New Dex order')
    # Dex orders are in the same place in both versions
    dex_order_offset = 0x040D60
    dex_abc_order_offset = 0x040C70
    dex_order_length = 252
    dex_position = 0
    for species_index in rom[dex_order_offset:dex_order_offset + dex_order_length]:
        species_slug = pokemon_slugs[species_index]
        out_pokemon[species_slug][version_group]['numbers']['original-johto'] = dex_position
        dex_position += 1
    dex_position = 0
    for species_index in rom[dex_abc_order_offset:dex_abc_order_offset + dex_order_length]:
        species_slug = pokemon_slugs[species_index]
        out_pokemon[species_slug][version_group]['numbers']['original-johto-abc'] = dex_position
        dex_position += 1

    growthrate_map = {
        0x00: 'medium',
        0x03: 'medium-slow',
        0x04: 'fast',
        0x05: 'slow',
    }
    egggroup_map = {
        0x1: 'monster',
        0x2: 'water1',
        0x3: 'bug',
        0x4: 'flying',
        0x5: 'ground',
        0x6: 'fairy',
        0x7: 'plant',
        0x8: 'humanshape',
        0x9: 'water3',
        0xA: 'mineral',
        0xB: 'indeterminate',
        0xC: 'water2',
        0xD: 'ditto',
        0xE: 'dragon',
        0xF: 'no-eggs',
    }
    # These chances come from
    # https://github.com/pret/pokecrystal/blob/9a927c1b3efa2eca886f346a4fcca0eb57278faf/engine/battle/core.asm#L5988
    held_item_chances = [23, 2]

    # Iterate over the pokemon base stats table
    if version_group == 'gold-silver':
        stats_offset = 0x51B0B
    else:
        stats_offset = 0x51424
    stats_length = 32
    print('Extracting base stats')
    for species_index, species_slug in pokemon_slugs.items():
        # Get Pokemon data entry
        start = stats_offset + ((species_index - 1) * stats_length)
        end = start + stats_length
        entry = rom[start:end]

        # Base stats
        stats = {
            'hp': {
                'base_value': entry[1],
                'effort_change': entry[1]
            },
            'attack': {
                'base_value': entry[2],
                'effort_change': entry[2]
            },
            'defense': {
                'base_value': entry[3],
                'effort_change': entry[3]
            },
            'speed': {
                'base_value': entry[4],
                'effort_change': entry[4]
            },
            'special-attack': {
                'base_value': entry[5],
                'effort_change': entry[5]
            },
            'special-defense': {
                'base_value': entry[6],
                # Sp. Attack and Sp. Defense share the same EV
                'effort_change': entry[5]
            },
        }
        type_1 = _type_map[entry[7]]
        type_2 = _type_map[entry[8]]
        capture_rate = entry[9]
        experience = entry[10]
        if entry[11] != 0x00 or entry[12] != 0x00:
            wild_held_items = {version: {}}
            for held_item_id, held_item_chance in zip(entry[11:13], held_item_chances):
                if held_item_id != 0x00:
                    wild_held_items[version][item_slugs[held_item_id]] = held_item_chance
        else:
            wild_held_items = {}
        female_rate = round(entry[13] / 255 * 100)
        hatch_steps = entry[15]

        # Sprite info we don't need occupies this gap
        growthrate = growthrate_map[entry[22]]
        egggroup_1 = (entry[23] >> 4) & 0x0F
        egggroup_2 = entry[23] & 0x0F
        if egggroup_1 == egggroup_2:
            egg_groups = [egggroup_map[egggroup_1]]
        else:
            egg_groups = [egggroup_map[egggroup_1], egggroup_map[egggroup_2]]

        if type_1 == type_2:
            types = [type_1]
        else:
            types = [type_1, type_2]

        out_pokemon[species_slug][version_group]['pokemon'] = {
            species_slug: {
                'name': out_pokemon[species_slug][version_group]['name'],
                'default': True,
                'capture_rate': capture_rate,
                'experience': experience,
                'types': types,
                'stats': stats,
                'growth_rate': growthrate,
                'female_rate': female_rate,
                'hatch_steps': hatch_steps,
                'egg_groups': egg_groups
            }
        }
        if len(wild_held_items) > 0:
            out_pokemon[species_slug][version_group]['pokemon'][species_slug]['wild_held_items'] = wild_held_items

        # TMs/HMs are stored in a bit field.  HMs 1-7 are TMs 51-57.
        tm_bits = int.from_bytes(entry[24:], byteorder='little', signed=False)
        for tm_num in range(1, 58):
            if (tm_bits & (1 << (tm_num - 1))) > 0:
                if tm_num <= 50:
                    machine_slug = 'tm{num:02}'.format(num=tm_num)
                else:
                    machine_slug = 'hm{num:02}'.format(num=tm_num - 50)
                out_pokemon_moves.append({
                    'species': species_slug,
                    'pokemon': species_slug,
                    'version_group': version_group,
                    'move': tm_moves[machine_slug],
                    'learn_method': 'machine',
                    'level': None,
                    'machine': machine_slug
                })
        if version_group == 'crystal':
            # Crystal also has three tutorable moves, stored as TMs 58-60.
            tutors = {
                58: 'flamethrower',
                59: 'thunderbolt',
                60: 'ice-beam',
            }
            for tm_num, taught_move in tutors.items():
                if (tm_bits & (1 << (tm_num - 1))) > 0:
                    out_pokemon_moves.append({
                        'species': species_slug,
                        'pokemon': species_slug,
                        'version_group': version_group,
                        'move': taught_move,
                        'learn_method': 'tutor',
                        'level': None,
                    })

    # Dex flavor (e.g. genus, height/weight, etc.)
    print('Extracting flavor')
    if version_group == 'gold-silver':
        flavor_offset = 0x044360
        flavor_banks = [0x68, 0x69, 0x6A, 0x6B]
    else:
        flavor_offset = 0x044378
        flavor_banks = [0x60, 0x6E, 0x73, 0x74]

    cursor = flavor_offset
    for species_index, species_slug in pokemon_slugs.items():
        dex_pointer = rom[cursor:cursor + 2]
        if species_index <= 64:
            dex_bank = flavor_banks[0]
        elif species_index <= 128:
            dex_bank = flavor_banks[1]
        elif species_index <= 192:
            dex_bank = flavor_banks[2]
        else:
            dex_bank = flavor_banks[3]
        dex_cursor = gb.address_from_pointer(dex_pointer, dex_bank)

        # Read the genus
        genus = bytearray()
        while rom[dex_cursor] != 0x50:
            genus += rom[dex_cursor].to_bytes(1, byteorder='little')
            dex_cursor += 1
        genus = genus.decode('pokemon_gen2') + ' Pokémon'
        dex_cursor += 1
        # The game uses weird text replacement to build the feet' inches" display
        height_parts = str(int.from_bytes(rom[dex_cursor:dex_cursor + 2], byteorder='little') / 100).split('.')
        height_ft = int(height_parts[0])
        height_in = int(height_parts[1])
        height = max(math.floor(((12 * height_ft) + height_in) / 3.937), 1)
        dex_cursor += 2
        weight_lbs = int.from_bytes(rom[dex_cursor:dex_cursor + 2], byteorder='little') / 10
        weight = max(1, math.floor(weight_lbs * 4.536))
        dex_cursor += 2
        dex_text = bytearray()
        # Two pages, separated by the EOF char.
        for i in range(2):
            while rom[dex_cursor] != 0x50:
                dex_text += rom[dex_cursor].to_bytes(1, byteorder='little')
                dex_cursor += 1
            dex_text += rom[dex_cursor].to_bytes(1, byteorder='little')
            dex_cursor += 1
        dex_text = dex_text.decode('pokemon_gen2').strip()
        flavor_text = {}
        flavor_text[version] = dex_text
        out_pokemon[species_slug][version_group]['pokemon'][species_slug].update({
            'genus': genus,
            'height': height,
            'weight': weight,
            'flavor_text': flavor_text,
        })

        cursor += 2

    # Evolution/Moves
    print('Extracting Pokemon evolution/learnset')
    if version_group == 'gold-silver':
        evo_moves_offset = 0x0427BD
    else:
        evo_moves_offset = 0x0425B1
    for species_index, species_slug in pokemon_slugs.items():
        start = evo_moves_offset + ((species_index - 1) * 2)
        end = start + 2
        pointer = rom[start:end]
        cursor = gb.address_from_pointer(pointer, 0x10)
        # Evolution
        # This is listed by evolution child, instead of parent as the dataset uses.
        # Some Pokémon (e.g. Eevee) have more than one evolution, these are back-to-back.
        # Evo data ends with 0x00.
        while rom[cursor] > 0x00:
            evo_type = rom[cursor]
            cursor += 1
            evo_conditions = {}
            if evo_type == 0x01:
                trigger = 'level-up'
                level = rom[cursor]
                cursor += 1
                evolves_into = rom[cursor]
                evo_conditions[trigger] = {
                    'minimum_level': level
                }
            elif evo_type == 0x02:
                trigger = 'use-item'
                item = item_slugs[rom[cursor]]
                cursor += 1
                evolves_into = rom[cursor]
                evo_conditions[trigger] = {
                    'trigger_item': item
                }
            elif evo_type == 0x03:
                trigger = 'trade'
                item_id = rom[cursor]
                cursor += 1
                evolves_into = rom[cursor]
                evo_conditions[trigger] = {}
                if item_id != 0xFF:
                    item = item_slugs[item_id]
                    evo_conditions[trigger]['held_item'] = item
            elif evo_type == 0x04:
                trigger = 'level-up'
                evo_conditions[trigger] = {'minimum_happiness': 220}
                if rom[cursor] == 0x02:
                    evo_conditions[trigger]['time_of_day'] = ['morning', 'day']
                elif rom[cursor] == 0x03:
                    evo_conditions[trigger]['time_of_day'] = ['night']
                cursor += 1
                evolves_into = rom[cursor]
            elif evo_type == 0x05:
                trigger = 'level-up'
                level = rom[cursor]
                cursor += 1
                stats_diff_map = {
                    0x01: 1,
                    0x02: -1,
                    0x03: 0,
                }
                evo_conditions[trigger] = {
                    'minimum_level': level,
                    'physical_stats_difference': stats_diff_map[rom[cursor]],
                }
                cursor += 1
                evolves_into = rom[cursor]
            else:
                raise Exception('Bad evolution type %s' % evo_type)

            evolves_into = pokemon_slugs[evolves_into]
            out_pokemon[evolves_into][version_group]['pokemon'][evolves_into][
                'evolution_parent'] = '{parent}/{parent}'.format(parent=species_slug)
            out_pokemon[evolves_into][version_group]['pokemon'][evolves_into]['evolution_conditions'] = evo_conditions
            cursor += 1
        cursor += 1
        while rom[cursor] > 0x00:
            level = rom[cursor]
            cursor += 1
            move = move_slugs[rom[cursor]]
            cursor += 1
            out_pokemon_moves.append({
                'species': species_slug,
                'pokemon': species_slug,
                'version_group': version_group,
                'move': move,
                'learn_method': 'level-up',
                'level': level,
                'machine': None
            })

    # Egg moves
    if version_group == 'gold-silver':
        egg_move_pointer_offset = 0x0239FE
    else:
        egg_move_pointer_offset = 0x023B11
    for species_index, species_slug in pokemon_slugs.items():
        start = egg_move_pointer_offset + ((species_index - 1) * 2)
        pointer = rom[start:start + 2]
        cursor = gb.address_from_pointer(pointer, 0x08)
        while rom[cursor] != 0xFF:
            move = rom[cursor]
            move = move_slugs[move]
            out_pokemon_moves.append({
                'species': species_slug,
                'pokemon': species_slug,
                'version_group': version_group,
                'move': move,
                'learn_method': 'egg',
                'level': None,
                'machine': None
            })
            cursor += 1

    # Media/Form fill-in
    print('Extracting Pokemon icons')
    if version == 'gold':
        icons_offset = 0x08E975
    elif version == 'silver':
        icons_offset = 0x08E95B
    else:
        icons_offset = 0x08EAC4
    icon_map = {
        # 0x00: 'null',
        0x01: 'poliwag',
        0x02: 'jigglypuff',
        0x03: 'diglett',
        0x04: 'pikachu',
        0x05: 'staryu',
        0x06: 'fish',
        0x07: 'bird',
        0x08: 'monster',
        0x09: 'clefairy',
        0x0A: 'oddish',
        0x0B: 'bug',
        0x0C: 'ghost',
        0x0D: 'lapras',
        0x0E: 'humanshape',
        0x0F: 'fox',
        0x10: 'equine',
        0x11: 'shell',
        0x12: 'blob',
        0x13: 'serpent',
        0x14: 'voltorb',
        0x15: 'squirtle',
        0x16: 'bulbasaur',
        0x17: 'charmander',
        0x18: 'caterpillar',
        0x19: 'unown',
        0x1A: 'geodude',
        0x1B: 'fighter',
        0x1C: 'egg',
        0x1D: 'jellyfish',
        0x1E: 'moth',
        0x1F: 'bat',
        0x20: 'snorlax',
        0x21: 'ho_oh',
        0x22: 'lugia',
        0x23: 'gyarados',
        0x24: 'slowpoke',
        0x25: 'sudowoodo',
        0x26: 'bigmon',
    }
    for species_index, species_slug in pokemon_slugs.items():
        icon = rom[icons_offset + species_index - 1]
        icon = icon_map[icon]
        if species_slug == 'unown':
            out_pokemon[species_slug][version_group]['pokemon'][species_slug]['forms'] = {}
            for letter in ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
                           's', 't', 'u', 'v', 'w', 'x', 'y', 'z']:
                if letter == 'a':
                    default = True
                else:
                    default = False
                out_pokemon[species_slug][version_group]['pokemon'][species_slug]['forms']['unown-' + letter] = {
                    'name': '{species} {letter}'.format(species=out_pokemon[species_slug][version_group]['name'],
                                                        letter=letter.upper()),
                    'form_name': letter.upper(),
                    'default': default,
                    'battle_only': False,
                    'sprites': [
                        '{version_group}/{species}-{letter}.png'.format(version_group=version_group,
                                                                        species=species_slug, letter=letter),
                        '{version_group}/back/{species}-{letter}.png'.format(version_group=version_group,
                                                                             species=species_slug, letter=letter),
                        '{version_group}/shiny/{species}-{letter}.png'.format(version_group=version_group,
                                                                              species=species_slug, letter=letter),
                    ],
                    'art': [
                        '{species}-f.png'.format(species=species_slug)
                    ],
                    'cry': 'gen5/{species}-a.webm'.format(species=species_slug),
                    'icon': 'gen2/{icon}.png'.format(icon=icon),
                }
        else:
            out_pokemon[species_slug][version_group]['pokemon'][species_slug]['forms'] = {
                species_slug: {
                    'name': out_pokemon[species_slug][version_group]['name'],
                    'form_name': 'Default Form',
                    'default': True,
                    'battle_only': False,
                    'sprites': [
                        '{version_group}/{species}-default.png'.format(version_group=version_group,
                                                                       species=species_slug),
                        '{version_group}/back/{species}-default.png'.format(version_group=version_group,
                                                                            species=species_slug),
                        '{version_group}/shiny/{species}-default.png'.format(version_group=version_group,
                                                                             species=species_slug),
                    ],
                    'art': [
                        '{species}-default.png'.format(species=species_slug)
                    ],
                    'cry': 'gen5/{species}-default.webm'.format(species=species_slug),
                    'icon': 'gen2/{icon}.png'.format(icon=icon),
                }
            }

    return out_pokemon, out_pokemon_moves


out_pokemon, out_pokemon_moves = get_pokemon()

if args.write_moves:
    print('Writing Moves')
    for move_slug, move_data in progressbar.progressbar(out_moves.items()):
        yaml_path = os.path.join(args.out_moves, '{slug}.yaml'.format(slug=move_slug))
        try:
            with open(yaml_path, 'r') as move_yaml:
                data = yaml.load(move_yaml.read())
        except IOError:
            data = {}
        data.update(move_data)
        with open(yaml_path, 'w') as move_yaml:
            yaml.dump(data, move_yaml)

    # Remove this version group's data from the new name file
    for old_name, new_name in move_name_changes.items():
        yaml_path = os.path.join(args.out_moves, '{slug}.yaml'.format(slug=new_name))
        with open(yaml_path, 'r') as move_yaml:
            data = yaml.load(move_yaml.read())
        try:
            del data[version_group]
        except KeyError:
            # No need to re-write this file
            continue
        with open(yaml_path, 'w') as move_yaml:
            yaml.dump(data, move_yaml)

if args.write_items:
    skip_items = []

    print('Writing Items')
    for item_slug, item_data in progressbar.progressbar(out_items.items()):
        if item_slug in skip_items:
            # There's a lot of bug items in the internal list
            continue
        yaml_path = os.path.join(args.out_items, '{slug}.yaml'.format(slug=item_slug))
        try:
            with open(yaml_path, 'r') as item_yaml:
                data = yaml.load(item_yaml.read())
        except IOError:
            data = {}
        data.update(item_data)
        with open(yaml_path, 'w') as item_yaml:
            yaml.dump(data, item_yaml)

    # Remove this version group's data from the new name file
    for old_name, new_name in item_name_changes.items():
        yaml_path = os.path.join(args.out_items, '{slug}.yaml'.format(slug=new_name))
        with open(yaml_path, 'r') as item_yaml:
            data = yaml.load(item_yaml.read())
        try:
            del data[version_group]
        except KeyError:
            # No need to re-write this file
            continue
        with open(yaml_path, 'w') as move_yaml:
            yaml.dump(data, move_yaml)

if args.write_pokemon:
    print('Writing Pokemon')
    for species_slug, species_data in progressbar.progressbar(out_pokemon.items()):
        yaml_path = os.path.join(args.out_pokemon, '{slug}.yaml'.format(slug=species_slug))
        try:
            with open(yaml_path, 'r') as pokemon_yaml:
                data = yaml.load(pokemon_yaml.read())
        except IOError:
            data = {}
        # Handle version-specific data
        # This allows running the script with both versions in the version group without loosing the other
        # version's data
        if version_group in data:
            if 'flavor_text' in data[version_group]['pokemon'][species_slug]:
                flavor_text = data[version_group]['pokemon'][species_slug]['flavor_text']
                flavor_text.update(species_data[version_group]['pokemon'][species_slug]['flavor_text'])
                species_data[version_group]['pokemon'][species_slug]['flavor_text'] = flavor_text
            if 'wild_held_items' in data[version_group]['pokemon'][species_slug]:
                wild_held_items = data[version_group]['pokemon'][species_slug]['wild_held_items']
                wild_held_items.update(species_data[version_group]['pokemon'][species_slug]['wild_held_items'])
                species_data[version_group]['pokemon'][species_slug]['wild_held_items'] = wild_held_items
        data.update(species_data)
        with open(yaml_path, 'w') as pokemon_yaml:
            yaml.dump(data, pokemon_yaml)

if args.write_pokemon_moves:
    print('Writing Pokemon moves')

    # Get existing data, removing those that have just been ripped.
    delete_learn_methods = [
        'level-up',
        'machine',
        'tutor',
        'egg'
    ]
    data = []
    with open(args.out_pokemon_moves, 'r') as pokemon_moves_csv:
        for row in progressbar.progressbar(csv.DictReader(pokemon_moves_csv)):
            if row['version_group'] != version_group or row['learn_method'] not in delete_learn_methods:
                data.append(row)

    data.extend(out_pokemon_moves)
    with open(args.out_pokemon_moves, 'w') as pokemon_moves_csv:
        writer = csv.DictWriter(pokemon_moves_csv, fieldnames=data[0].keys())
        writer.writeheader()
        for row in progressbar.progressbar(data):
            writer.writerow(row)
