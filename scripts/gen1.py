# Rip Gen 1 pokemon data

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
    0x07: 'bug',
    0x08: 'ghost',
    0x14: 'fire',
    0x15: 'water',
    0x16: 'grass',
    0x17: 'electric',
    0x18: 'psychic',
    0x19: 'ice',
    0x1A: 'dragon',
}

# Get config
argparser = argparse.ArgumentParser(description='Load Gen 1 data.')
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
    'POKEMON RED': 'red',
    'POKEMON BLUE': 'blue',
    'POKEMON YELLOW': 'yellow'
}
versiongroupmap = {
    'red': 'red-blue',
    'blue': 'red-blue',
    'yellow': 'yellow'
}

version = rom[0x134:0x143].rstrip(b'\x00')
version = version.decode('ascii')
version = versionmap[version]
version_group = versiongroupmap[version]
print('Using version group {version_group}'.format(version_group=version_group))

# Slug maps keyed by internal id
move_slugs = {}
move_name_changes = {
    'ancientpower': 'ancient-power',
    'bubblebeam': 'bubble-beam',
    'doubleslap': 'double-slap',
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
    if version_group == 'red-blue':
        move_names_offset = 0x0B0000
    else:
        move_names_offset = 0x0BC000
    move_names = {}
    name = bytearray()
    move_id = 1
    print('Extracting move names')
    for char in rom[move_names_offset:]:
        if char == 0x50:
            decoded_name = name.decode('pokemon_gen1')
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
    move_data_length = 6
    if version_group == 'red-blue':
        move_data_offset = 0x038000
    else:
        move_data_offset = 0x038000

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
        'effect_chance',
    ]
    # move slug => effect id
    fixed_damage_moves = {
        'sonicboom': 131,
        'seismic-toss': 88,
        'dragon-rage': 42,
        'night-shade': 88,
        'psywave': 89,
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
        if out[move_slug][version_group]['power'] == 0:
            del out[move_slug][version_group]['power']
        if move_slug in fixed_damage_moves:
            # The game uses the same effect id for different damage amounts
            out[move_slug][version_group][effect_id] = fixed_damage_moves[move_slug]

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
    # Get item names and IDs
    if version_group == 'yellow':
        itemnames = rom[0x45B7:0x491E]
    else:
        itemnames = rom[0x472B:0x4A91]
    iteminfo = {}
    itemid = 1
    itemnames_list = itemnames.split(b'\x50')
    print('Extracting item names')
    for namedata in itemnames_list:
        name = namedata.decode(encoding='pokemon_gen1', errors='ignore')
        slug = slugify.slugify(name)
        iteminfo[itemid] = {
            'slug': slug,
            'name': name,
            'pocket': 'misc',
            'buy': None,
            'sell': None
        }
        item_slugs[itemid] = slug
        itemid = itemid + 1

    # Add TM/HM to the item table
    for machine in range(1, 51):
        name = 'TM{:02}'.format(machine)
        itemid = 0xC8 + machine
        iteminfo[itemid] = {
            'slug': slugify.slugify(name),
            'name': name,
            'pocket': 'misc',
            'buy': None,
            'sell': None
        }
    for machine in range(1, 6):
        name = 'HM{:02}'.format(machine)
        itemid = 0xC3 + machine
        iteminfo[itemid] = {
            'slug': slugify.slugify(name),
            'name': name,
            'pocket': 'misc',
            'buy': None,
            'sell': None
        }

    # item prices
    if version_group == 'yellow':
        itemprices = rom[0x4494:0x45B6]
    else:
        itemprices = rom[0x4608:0x472A]
    pricebytes = bytearray()
    itemid = 1
    print('Extracting item prices')
    for byte in itemprices:
        # The item prices are stored as binary-coded decimal in three bytes.
        pricebytes.append(byte)
        if len(pricebytes) < 3:
            continue

        price = int(pricebytes.hex())
        # Special case for the bicycle
        if iteminfo[itemid]['slug'] == 'bicycle':
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
    if version_group == 'yellow':
        shops = rom[0x233B:0x23D0]
    else:
        shops = rom[0x2442:0x24D6]
    shops = shops.split(b'\xFF\xFE')
    out_shops = []
    shopid = 0
    print('Dumping shop data')
    for shopdata in shops:
        if shop_order[shopid] is None:
            # This will skip the dummy shop in the game data.
            continue

        shopdata = shopdata.lstrip(b'\xFE').rstrip(b'\xFF')
        shoplocationparts = shop_order[shopid].split('/')
        shoplocation = shoplocationparts.pop(0)
        shopidentifier = shoplocationparts.pop(-1)
        shoparea = '/'.join(shoplocationparts)
        for itemid in shopdata[1:]:
            out_shops.append({
                'version_group': version_group,
                'location': shoplocation,
                'area': shoparea,
                'shop': shopidentifier,
                'item': iteminfo[itemid]['slug'],
                'buy': iteminfo[itemid]['buy']
            })
        shopid = shopid + 1

    # Machine descriptions
    if version_group == 'yellow':
        machinemoves = rom[0x01232D:0x012365]
    else:
        machinemoves = rom[0x013773:0x0137AA]
    re_ismachine = re.compile(r'^(?P<type>tm|hm)(?P<number>\d{2})$', flags=re.IGNORECASE)
    for item in iteminfo.values():
        match = re_ismachine.match(item['slug'])
        if not match:
            continue
        searchnumber = int(match.group('number'))
        if match.group('type') == 'hm':
            searchnumber = searchnumber + 50
        move_id = int(machinemoves[searchnumber - 1])
        tm_moves[item['slug']] = move_slugs[move_id]
        short_description = 'Teaches []{{move:{move}}} to a compatible Pokèmon.'.format(move=move_slugs[move_id])
        description = r'''
    Teaches []{{move:{move}}} to a compatible Pokèmon.

    {{{{App\Controller\ItemController::tmPokemon({{"itemSlug": "{item}"}})}}}}
            '''.format(move=move_slugs[move_id], item=item['slug']).strip()
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
        slug = item['slug']
        del item['slug']

        if not slug:
            # Skip dummy items
            continue

        # Read the existing file to add to it.
        filename = 'item/{name}.yaml'.format(name=slug)
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

        out_items[slug] = {version_group: item}

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
    if version_group == 'red-blue':
        names_offset = 0x01C21E
    else:
        names_offset = 0x0E8000
    name_length = 10
    for species_index in range(1, 191):
        start = names_offset + ((species_index - 1) * name_length)
        end = start + name_length
        entry = rom[start:end]
        name = entry.decode('pokemon_gen1').strip()
        slug = slugify.slugify(name.replace('♀', '-f').replace('♂', '-m'))
        if slug in slug_overrides:
            slug = slug_overrides[slug]
        if slug == 'missingno':
            # Nope nope nope
            continue
        pokemon_slugs[species_index] = slug
        out_pokemon[slug] = {
            version_group: {
                'name': name
            }
        }

    # Pokedex order
    print('Extracting Dex order')
    if version_group == 'red-blue':
        dex_order_offset = 0x041024
    else:
        dex_order_offset = 0x0410B1
    dex_order = rom[dex_order_offset:dex_order_offset + 190]
    for species_index, slug in pokemon_slugs.items():
        position = dex_order[species_index - 1]
        out_pokemon[slug][version_group]['position'] = position
        out_pokemon[slug][version_group]['numbers'] = {
            'national': position,
            'kanto': position
        }

    growthrate_map = {
        0x00: 'medium',
        0x03: 'medium-slow',
        0x04: 'fast',
        0x05: 'slow',
    }

    # Iterate over the pokemon base stats table
    # It's in the same location in both red/blue and yellow.
    # This info is stored in dex order, not id order.
    stats_offset = 0x383DE
    stats_length = 28
    print('Extracting base stats')
    for species_index, species_slug in pokemon_slugs.items():
        # Get Pokemon data entry
        # Mew is stored in a different place in red/blue
        if species_slug == 'mew' and version_group == 'red-blue':
            start = 0x425B
        else:
            start = stats_offset + ((dex_order[species_index - 1] - 1) * stats_length)
        end = start + stats_length
        entry = rom[start:end]

        # Base stats
        stats = {
            'hp': entry[0x01],
            'attack': entry[0x02],
            'defense': entry[0x03],
            'speed': entry[0x04],
            'special': entry[0x05]
        }
        type_1 = _type_map[entry[0x06]]
        type_2 = _type_map[entry[0x07]]
        capture_rate = entry[0x08]
        experience = entry[0x09]
        # Sprite info we don't need occupies this gap
        # The level 1 moves are stored here
        for move_id in entry[0x0F:0x13]:
            if move_id == 0x00:
                continue
            out_pokemon_moves.append({
                'species': species_slug,
                'pokemon': species_slug,
                'version_group': version_group,
                'move': move_slugs[move_id],
                'learn_method': 'level-up',
                'level': 1,
                'machine': None
            })
        growthrate = growthrate_map[entry[0x13]]

        # TMs/HMs are stored in a bit field.  HMs 1-5 are TMs 51-55.
        tm_bits = int.from_bytes(entry[0x14:0x1B], byteorder='little', signed=False)
        for tm_num in range(1, 56):
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
                'stats': {},
                'growth_rate': growthrate
            }
        }
        for stat, value in stats.items():
            # Gen 1/2 use a different EV system than modern games.
            out_pokemon[species_slug][version_group]['pokemon'][species_slug]['stats'][stat] = {
                'base_value': value,
                'effort_change': value
            }

    # Dex flavor (e.g. genus, height/weight, etc.)
    print('Extracting flavor')
    if version_group == 'red-blue':
        flavor_offset = 0x0405FA
        # Stored in ID order
        flavor_slugs = {}
        i = 0
        for slug in pokemon_slugs.values():
            flavor_slugs[i] = slug
            i += 1
    else:
        flavor_offset = 0x040687
        # Stored in dex order
        flavor_slugs = {}
        for species_index, slug in pokemon_slugs.items():
            flavor_slugs[dex_order[species_index - 1] - 1] = slug

    cursor = flavor_offset
    for flavor_index, slug in flavor_slugs.items():
        # Read the genus
        genus = bytearray()
        while rom[cursor] != 0x50:
            genus += rom[cursor].to_bytes(1, byteorder='little')
            cursor += 1
        genus = genus.decode('pokemon_gen1') + ' Pokémon'
        cursor += 1
        height_ft = rom[cursor]
        cursor += 1
        height_in = rom[cursor]
        cursor += 1
        height = math.floor(((12 * height_ft) + height_in) / 3.937)
        weight_lbs = int.from_bytes(rom[cursor:cursor + 2], byteorder='little')
        weight = math.floor(weight_lbs * 4.536)
        cursor += 2
        # Indicates pointer to text
        assert rom[cursor] == 0x17
        cursor += 1
        text_pointer = rom[cursor:cursor + 2]
        cursor += 2
        text_bank = rom[cursor]
        text_cursor = gb.address_from_pointer(text_pointer, text_bank)
        dex_text = bytearray()
        while rom[text_cursor] != 0x50:
            dex_text += rom[text_cursor].to_bytes(1, byteorder='little')
            text_cursor += 1
        cursor += 1
        # This should be the end of the entry
        assert rom[cursor] == 0x50
        cursor += 1
        dex_text = dex_text.decode('pokemon_gen1').strip()
        flavor_text = {}
        for version in version_group.split('-'):
            flavor_text[version] = dex_text
        out_pokemon[slug][version_group]['pokemon'][slug].update({
            'genus': genus,
            'height': height,
            'weight': weight,
            'flavor_text': flavor_text,
        })

    # Evolution/Moves
    print('Extracting Pokemon evolution/learnset')
    evo_method_map = {
        0x00: None,
        0x01: 'level-up',
        0x02: 'use-item',
        0x03: 'trade',
    }
    if version_group == 'red-blue':
        evo_moves_offset = 0x03B05C
    else:
        evo_moves_offset = 0x03B1E5
    for species_index, species_slug in pokemon_slugs.items():
        start = evo_moves_offset + ((species_index - 1) * 2)
        end = start + 2
        pointer = rom[start:end]
        cursor = gb.address_from_pointer(pointer, 0x0E)
        # Evolution
        # This is listed by evolution child, instead of parent as the dataset uses.
        # Some Pokémon (i.e. Eevee) have more than one evolution, these are back-to-back.
        # Evo data ends with 0x00.
        while rom[cursor] > 0x00:
            method = evo_method_map[rom[cursor]]
            evo_conditions = {method: {}}
            cursor += 1
            if method == 'use-item':
                item = item_slugs[rom[cursor]]
                evo_conditions[method]['trigger_item'] = item
                cursor += 1
            level = rom[cursor]
            if level > 1:
                evo_conditions[method]['minimum_level'] = level
            cursor += 1
            evolves_into = rom[cursor]
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

    # Media/Form fill-in
    print('Extracting Pokemon icons')
    if version_group == 'red-blue':
        icons_offset = 0x07190D
    else:
        icons_offset = 0x0719BA
    icon_map = {
        0x0: 'mon',
        0x1: 'ball_m',
        0x2: 'helix',
        0x3: 'fairy',
        0x4: 'bird_m',
        0x5: 'water',
        0x6: 'bug',
        0x7: 'grass',
        0x8: 'snake',
        0x9: 'quadruped',
        0xA: 'pikachu_family',
    }
    # Icons are stored as nybbles
    for species_index, species_slug in pokemon_slugs.items():
        lookup_index = (dex_order[species_index - 1] - 1) // 2
        byte = rom[icons_offset + lookup_index]
        if dex_order[species_index - 1] % 2 == 1:
            icon = (byte >> 4) & 0x0F
        else:
            icon = byte & 0x0F
        icon = icon_map[icon]
        out_pokemon[species_slug][version_group]['pokemon'][species_slug]['forms'] = {
            species_slug: {
                'name': out_pokemon[species_slug][version_group]['name'],
                'form_name': 'Default Form',
                'default': True,
                'battle_only': False,
                'sprites': [
                    '{version_group}/{species}-default.png'.format(version_group=version_group, species=species_slug),
                    '{version_group}/gray/{species}-default.png'.format(version_group=version_group,
                                                                        species=species_slug),
                    '{version_group}/back/{species}-default.png'.format(version_group=version_group,
                                                                        species=species_slug),
                ],
                'art': [
                    '{species}-default.png'.format(species=species_slug)
                ],
                'cry': 'gen5/{species}-default.webm'.format(species=species_slug),
                'icon': 'gen1/{icon}.png'.format(icon=icon),
            }
        }

    return out_pokemon, out_pokemon_moves


out_pokemon, out_pokemon_moves = get_pokemon()


def get_encounters():
    print('Dumping encounters')
    out = []
    map_slugs = {
        0x00: {'location': 'pallet-town', 'area': 'whole-area'},
        0x01: {'location': 'viridian-city', 'area': 'whole-area'},
        0x02: {'location': 'pewter-city', 'area': 'whole-area'},
        0x03: {'location': 'cerulean-city', 'area': 'whole-area'},
        0x04: {'location': 'lavender-town', 'area': 'whole-area'},
        0x05: {'location': 'vermilion-city', 'area': 'whole-area'},
        0x06: {'location': 'celadon-city', 'area': 'whole-area'},
        0x07: {'location': 'fuchsia-city', 'area': 'whole-area'},
        0x08: {'location': 'cinnabar-island', 'area': 'whole-area'},
        0x09: {'location': 'indigo-plateau', 'area': 'whole-area'},
        0x0A: {'location': 'saffron-city', 'area': 'whole-area'},
        0x0C: {'location': 'kanto-route-1', 'area': 'whole-area'},
        0x0D: {'location': 'kanto-route-2', 'area': 'whole-area'},
        0x0E: {'location': 'kanto-route-3', 'area': 'whole-area'},
        0x0F: {'location': 'kanto-route-4', 'area': 'whole-area'},
        0x10: {'location': 'kanto-route-5', 'area': 'whole-area'},
        0x11: {'location': 'kanto-route-6', 'area': 'whole-area'},
        0x12: {'location': 'kanto-route-7', 'area': 'whole-area'},
        0x13: {'location': 'kanto-route-8', 'area': 'whole-area'},
        0x14: {'location': 'kanto-route-9', 'area': 'whole-area'},
        0x15: {'location': 'kanto-route-10', 'area': 'whole-area'},
        0x16: {'location': 'kanto-route-11', 'area': 'whole-area'},
        0x17: {'location': 'kanto-route-12', 'area': 'whole-area'},
        0x18: {'location': 'kanto-route-13', 'area': 'whole-area'},
        0x19: {'location': 'kanto-route-14', 'area': 'whole-area'},
        0x1A: {'location': 'kanto-route-15', 'area': 'whole-area'},
        0x1B: {'location': 'kanto-route-16', 'area': 'whole-area'},
        0x1C: {'location': 'kanto-route-17', 'area': 'whole-area'},
        0x1D: {'location': 'kanto-route-18', 'area': 'whole-area'},
        0x1E: {'location': 'kanto-route-19', 'area': 'whole-area'},
        0x1F: {'location': 'kanto-route-20', 'area': 'whole-area'},
        0x20: {'location': 'kanto-route-21', 'area': 'whole-area'},
        0x21: {'location': 'kanto-route-22', 'area': 'whole-area'},
        0x22: {'location': 'kanto-route-23', 'area': 'whole-area'},
        0x23: {'location': 'kanto-route-24', 'area': 'whole-area'},
        0x24: {'location': 'kanto-route-25', 'area': 'whole-area'},
        0x25: {'location': 'pallet-town', 'area': 'reds-house-1f'},
        0x26: {'location': 'pallet-town', 'area': 'reds-house-2f'},
        0x27: {'location': 'pallet-town', 'area': 'blues-house'},
        0x28: {'location': 'pallet-town', 'area': 'oaks-lab'},
        0x29: {'location': 'viridian-city', 'area': 'pokemon-center'},
        0x2A: {'location': 'viridian-city', 'area': 'mart'},
        0x2B: {'location': 'viridian-city', 'area': 'school'},
        0x2C: {'location': 'viridian-city', 'area': 'house-spearow'},
        0x2D: {'location': 'viridian-city', 'area': 'gym'},
        0x2E: {'location': 'digletts-cave', 'area': 'route-2'},
        0x2F: {'location': 'viridian-forest', 'area': 'north-gate'},
        0x30: {'location': 'kanto-route-2', 'area': 'house'},
        0x31: {'location': 'kanto-route-2', 'area': 'gate'},
        0x32: {'location': 'viridian-forest', 'area': 'south-gate'},
        0x33: {'location': 'viridian-forest', 'area': 'whole-area'},
        0x34: {'location': 'pewter-city', 'area': 'museum-1f'},
        0x35: {'location': 'pewter-city', 'area': 'museum-2f'},
        0x36: {'location': 'pewter-city', 'area': 'gym'},
        0x37: {'location': 'pewter-city', 'area': 'house-nidoran'},
        0x38: {'location': 'pewter-city', 'area': 'mart'},
        0x39: {'location': 'pewter-city', 'area': 'house'},
        0x3A: {'location': 'pewter-city', 'area': 'pokemon-center'},
        0x3B: {'location': 'mt-moon', 'area': '1f'},
        0x3C: {'location': 'mt-moon', 'area': 'b1f'},
        0x3D: {'location': 'mt-moon', 'area': 'b2f'},
        0x3E: {'location': 'cerulean-city', 'area': 'burgled-house'},
        0x3F: {'location': 'cerulean-city', 'area': 'house-jynx'},
        0x40: {'location': 'cerulean-city', 'area': 'pokemon-center'},
        0x41: {'location': 'cerulean-city', 'area': 'gym'},
        0x42: {'location': 'cerulean-city', 'area': 'bike-shop'},
        0x43: {'location': 'cerulean-city', 'area': 'mart'},
        0x44: {'location': 'kanto-route-4', 'area': 'pokemon-center'},
        # 0x45: {'location': 'cerulean-trashed-house-copy', 'area': 'whole-area'},
        0x46: {'location': 'kanto-route-5', 'area': 'south-gate'},
        0x47: {'location': 'kanto-underground-path-5-6', 'area': 'route-5'},
        0x48: {'location': 'kanto-route-5', 'area': 'daycare'},
        0x49: {'location': 'kanto-route-6', 'area': 'north-gate'},
        0x4A: {'location': 'kanto-underground-path-5-6', 'area': 'route-6'},
        # 0x4B: {'location': 'underground-path-route-6-copy', 'area': 'whole-area'},
        0x4C: {'location': 'kanto-route-7', 'area': 'east-gate'},
        0x4D: {'location': 'kanto-underground-path-7-8', 'area': 'route-7'},
        # 0x4E: {'location': 'underground-path-route-7-copy', 'area': 'whole-area'},
        0x4F: {'location': 'route-8-gate', 'area': 'whole-area'},
        0x50: {'location': 'kanto-underground-path-7-8', 'area': 'route-8'},
        0x51: {'location': 'kanto-route-10', 'area': 'pokemon-center'},
        0x52: {'location': 'rock-tunnel', 'area': 'b1f'},
        0x53: {'location': 'power-plant', 'area': 'whole-area'},
        0x54: {'location': 'kanto-route-11', 'area': 'east-gate-1f'},
        0x55: {'location': 'digletts-cave', 'area': 'route-11'},
        0x56: {'location': 'kanto-route-11', 'area': 'east-gate-2f'},
        0x57: {'location': 'kanto-route-12', 'area': 'gate-1f'},
        0x58: {'location': 'kanto-route-25', 'area': 'bills-house'},
        0x59: {'location': 'vermilion-city', 'area': 'pokemon-center'},
        0x5A: {'location': 'vermilion-city', 'area': 'pokemon-fan-club'},
        0x5B: {'location': 'vermilion-city', 'area': 'mart'},
        0x5C: {'location': 'vermilion-city', 'area': 'gym'},
        0x5D: {'location': 'vermilion-city', 'area': 'house-pidgey'},
        0x5E: {'location': 'ss-anne', 'area': 'dock'},
        0x5F: {'location': 'ss-anne', 'area': '1f'},
        0x60: {'location': 'ss-anne', 'area': '2f'},
        0x61: {'location': 'ss-anne', 'area': '3f'},
        0x62: {'location': 'ss-anne', 'area': 'b1f'},
        0x63: {'location': 'ss-anne', 'area': 'bow'},
        0x64: {'location': 'ss-anne', 'area': 'kitchen'},
        0x65: {'location': 'ss-anne', 'area': 'captains-room'},
        0x66: {'location': 'ss-anne', 'area': '1f-rooms'},
        0x67: {'location': 'ss-anne', 'area': '2f-rooms'},
        0x68: {'location': 'ss-anne', 'area': 'b1f-rooms'},
        # 0x69: {'location': 'unused-map-69', 'area': 'whole-area'},
        # 0x6A: {'location': 'unused-map-6a', 'area': 'whole-area'},
        # 0x6B: {'location': 'unused-map-6b', 'area': 'whole-area'},
        0x6C: {'location': 'kanto-victory-road', 'area': '1f'},
        # 0x6D: {'location': 'unused-map-6d', 'area': 'whole-area'},
        # 0x6E: {'location': 'unused-map-6e', 'area': 'whole-area'},
        # 0x6F: {'location': 'unused-map-6f', 'area': 'whole-area'},
        # 0x70: {'location': 'unused-map-70', 'area': 'whole-area'},
        0x71: {'location': 'indigo-plateau', 'area': 'lances-room'},
        # 0x72: {'location': 'unused-map-72', 'area': 'whole-area'},
        # 0x73: {'location': 'unused-map-73', 'area': 'whole-area'},
        # 0x74: {'location': 'unused-map-74', 'area': 'whole-area'},
        # 0x75: {'location': 'unused-map-75', 'area': 'whole-area'},
        0x76: {'location': 'indigo-plateau', 'area': 'hall-of-fame'},
        0x77: {'location': 'kanto-underground-path-5-6', 'area': 'underground'},
        0x78: {'location': 'indigo-plateau', 'area': 'champions-room'},
        0x79: {'location': 'kanto-underground-path-7-8', 'area': 'underground'},
        0x7A: {'location': 'celadon-city', 'area': 'department-store-1f'},
        0x7B: {'location': 'celadon-city', 'area': 'department-store-2f'},
        0x7C: {'location': 'celadon-city', 'area': 'department-store-3f'},
        0x7D: {'location': 'celadon-city', 'area': 'department-store-4f'},
        0x7E: {'location': 'celadon-city', 'area': 'department-store-roof'},
        0x7F: {'location': 'celadon-city', 'area': 'department-store-elevator'},
        0x80: {'location': 'celadon-city', 'area': 'mansion-1f'},
        0x81: {'location': 'celadon-city', 'area': 'mansion-2f'},
        0x82: {'location': 'celadon-city', 'area': 'mansion-3f'},
        0x83: {'location': 'celadon-city', 'area': 'mansion-roof'},
        0x84: {'location': 'celadon-city', 'area': 'mansion-roof-house'},
        0x85: {'location': 'celadon-city', 'area': 'pokemon-center'},
        0x86: {'location': 'celadon-city', 'area': 'gym'},
        0x87: {'location': 'celadon-city', 'area': 'game-corner'},
        0x88: {'location': 'celadon-city', 'area': 'department-store-5f'},
        0x89: {'location': 'celadon-city', 'area': 'game-corner-prize-room'},
        0x8A: {'location': 'celadon-city', 'area': 'diner'},
        0x8B: {'location': 'celadon-city', 'area': 'house-boss'},  # The game corner boss' house
        0x8C: {'location': 'celadon-city', 'area': 'hotel'},
        0x8D: {'location': 'lavender-town', 'area': 'pokemon-center'},
        0x8E: {'location': 'pokemon-tower', 'area': '1f'},
        0x8F: {'location': 'pokemon-tower', 'area': '2f'},
        0x90: {'location': 'pokemon-tower', 'area': '3f'},
        0x91: {'location': 'pokemon-tower', 'area': '4f'},
        0x92: {'location': 'pokemon-tower', 'area': '5f'},
        0x93: {'location': 'pokemon-tower', 'area': '6f'},
        0x94: {'location': 'pokemon-tower', 'area': '7f'},
        0x95: {'location': 'lavender-town', 'area': 'mr-fujis-house'},
        0x96: {'location': 'lavender-town', 'area': 'mart'},
        0x97: {'location': 'lavender-town', 'area': 'cubone-house'},
        0x98: {'location': 'fuchsia-city', 'area': 'mart'},
        0x99: {'location': 'fuchsia-city', 'area': 'bills-grandpas-house'},
        0x9A: {'location': 'fuchsia-city', 'area': 'pokemon-center'},
        0x9B: {'location': 'fuchsia-city', 'area': 'wardens-house'},
        0x9C: {'location': 'kanto-safari-zone', 'area': 'gate'},
        0x9D: {'location': 'fuchsia-city', 'area': 'gym'},
        0x9E: {'location': 'fuchsia-city', 'area': 'safari-zone-staff'},
        0x9F: {'location': 'seafoam-islands', 'area': 'b1f'},
        0xA0: {'location': 'seafoam-islands', 'area': 'b2f'},
        0xA1: {'location': 'seafoam-islands', 'area': 'b3f'},
        0xA2: {'location': 'seafoam-islands', 'area': 'b4f'},
        0xA3: {'location': 'vermilion-city', 'area': 'fishing-guru'},
        0xA4: {'location': 'fuchsia-city', 'area': 'fishing-guru'},
        0xA5: {'location': 'pokemon-mansion', 'area': '1f'},
        0xA6: {'location': 'cinnabar-island', 'area': 'gym'},
        0xA7: {'location': 'cinnabar-island', 'area': 'lab'},
        0xA8: {'location': 'cinnabar-island', 'area': 'lab-meeting-room'},
        0xA9: {'location': 'cinnabar-island', 'area': 'lab r-d-room'},
        0xAA: {'location': 'cinnabar-island', 'area': 'testing-room'},
        0xAB: {'location': 'cinnabar-island', 'area': 'pokemon-center'},
        0xAC: {'location': 'cinnabar-island', 'area': 'mart'},
        # 0xAD: {'location': 'cinnabar-mart-copy', 'area': 'whole-area'},
        0xAE: {'location': 'indigo-plateau', 'area': 'lobby'},
        0xAF: {'location': 'saffron-city', 'area': 'copycats-house-1f'},
        0xB0: {'location': 'saffron-city', 'area': 'copycats-house-2f'},
        0xB1: {'location': 'saffron-city', 'area': 'fighting-dojo'},
        0xB2: {'location': 'saffron-city', 'area': 'gym'},
        0xB3: {'location': 'saffron-city', 'area': 'house-pidgey'},
        0xB4: {'location': 'saffron-mart', 'area': 'whole-area'},
        0xB5: {'location': 'silph-co', 'area': '1f'},
        0xB6: {'location': 'saffron-city', 'area': 'pokemon-center'},
        0xB7: {'location': 'saffron-city', 'area': 'mr-psychics-house'},
        0xB8: {'location': 'kanto-route-15', 'area': 'west-gate-1f'},
        0xB9: {'location': 'kanto-route-15', 'area': 'west-gate-2f'},
        0xBA: {'location': 'kanto-route-16', 'area': 'gate-1f'},
        0xBB: {'location': 'kanto-route-16', 'area': 'gate-2f'},
        0xBC: {'location': 'kanto-route-16', 'area': 'recluses-house'},
        0xBD: {'location': 'kanto-route-12', 'area': 'fishing-guru'},
        0xBE: {'location': 'kanto-route-18', 'area': 'gate-1f'},
        0xBF: {'location': 'kanto-route-18', 'area': 'gate-2f'},
        0xC0: {'location': 'seafoam-islands', 'area': '1f'},
        0xC1: {'location': 'route-23', 'area': 'entrance'},
        0xC2: {'location': 'kanto-victory-road', 'area': '2f'},
        0xC3: {'location': 'route-12', 'area': 'gate-2f'},
        0xC4: {'location': 'vermilion-city', 'area': 'house-girl'},
        0xC5: {'location': 'digletts-cave', 'area': 'underground'},
        0xC6: {'location': 'kanto-victory-road', 'area': '3f'},
        0xC7: {'location': 'rocket-hideout', 'area': 'b1f'},
        0xC8: {'location': 'rocket-hideout', 'area': 'b2f'},
        0xC9: {'location': 'rocket-hideout', 'area': 'b3f'},
        0xCA: {'location': 'rocket-hideout', 'area': 'b4f'},
        0xCB: {'location': 'rocket-hideout', 'area': 'elevator'},
        # 0xCC: {'location': 'unused-map-cc', 'area': 'whole-area'},
        # 0xCD: {'location': 'unused-map-cd', 'area': 'whole-area'},
        # 0xCE: {'location': 'unused-map-ce', 'area': 'whole-area'},
        0xCF: {'location': 'silph-co', 'area': '2f'},
        0xD0: {'location': 'silph-co', 'area': '3f'},
        0xD1: {'location': 'silph-co', 'area': '4f'},
        0xD2: {'location': 'silph-co', 'area': '5f'},
        0xD3: {'location': 'silph-co', 'area': '6f'},
        0xD4: {'location': 'silph-co', 'area': '7f'},
        0xD5: {'location': 'silph-co', 'area': '8f'},
        0xD6: {'location': 'pokemon-mansion', 'area': '2f'},
        0xD7: {'location': 'pokemon-mansion', 'area': '3f'},
        0xD8: {'location': 'pokemon-mansion', 'area': 'b1f'},
        0xD9: {'location': 'kanto-safari-zone', 'area': 'area-1-east'},
        0xDA: {'location': 'kanto-safari-zone', 'area': 'area-2-north'},
        0xDB: {'location': 'kanto-safari-zone', 'area': 'area-3-west'},
        0xDC: {'location': 'kanto-safari-zone', 'area': 'middle'},
        0xDD: {'location': 'kanto-safari-zone', 'area': 'middle-rest-house'},
        0xDE: {'location': 'kanto-safari-zone', 'area': 'area-3-west-secret-house'},
        0xDF: {'location': 'kanto-safari-zone', 'area': 'area-3-west-rest-house'},
        0xE0: {'location': 'kanto-safari-zone', 'area': 'area-1-east-rest-house'},
        0xE1: {'location': 'kanto-safari-zone', 'area': 'area-2-north-rest-house'},
        0xE2: {'location': 'cerulean-cave', 'area': '2f'},
        0xE3: {'location': 'cerulean-cave', 'area': 'b1f'},
        0xE4: {'location': 'cerulean-cave', 'area': '1f'},
        0xE5: {'location': 'lavender-town', 'area': 'name-raters-house'},
        0xE6: {'location': 'cerulean-city', 'area': 'badge-mans-house'},
        # 0xE7: {'location': 'unused-map-e7', 'area': 'whole-area'},
        0xE8: {'location': 'rock-tunnel', 'area': 'b2f'},
        0xE9: {'location': 'silph-co', 'area': '9f'},
        0xEA: {'location': 'silph-co', 'area': '10f'},
        0xEB: {'location': 'silph-co', 'area': '11f'},
        0xEC: {'location': 'silph-co', 'area': 'elevator'},
        # 0xED: {'location': 'unused-map-ed', 'area': 'whole-area'},
        # 0xEE: {'location': 'unused-map-ee', 'area': 'whole-area'},
        # 0xEF: {'location': 'trade-center', 'area': 'whole-area'},
        # 0xF0: {'location': 'colosseum', 'area': 'whole-area'},
        # 0xF1: {'location': 'unused-map-f1', 'area': 'whole-area'},
        # 0xF2: {'location': 'unused-map-f2', 'area': 'whole-area'},
        # 0xF3: {'location': 'unused-map-f3', 'area': 'whole-area'},
        # 0xF4: {'location': 'unused-map-f4', 'area': 'whole-area'},
        0xF5: {'location': 'indigo-plateau', 'area': 'loreleis-room'},
        0xF6: {'location': 'indigo-plateau', 'area': 'brunos-room'},
        0xF7: {'location': 'indigo-plateau', 'area': 'agathas-room'},
    }

    # These percent chances are rounded off a tiny bit
    walk_slot_chances = [20, 20, 15, 10, 10, 10, 5, 5, 4, 1]
    if version_group == 'red-blue':
        grass_encounters_offset = 0xCEEB
        super_rod_offset = 0xE919
    else:
        grass_encounters_offset = 0xCB95
        super_rod_offset = 0x0F5EDA

    # Get the fishing groups, which work entirely different in yellow
    fishing_groups = {}
    if version_group == 'red-blue':
        cursor = super_rod_offset
        while rom[cursor] != 0xFF:
            map_id = rom[cursor]
            cursor += 1
            pointer = rom[cursor:cursor + 2]
            cursor += 2
            fishing_group_start = gb.address_from_pointer(pointer, 0x03)
            fishing_group_size = rom[fishing_group_start]
            fishing_groups[map_id] = []
            for fishing_group_member in range(0, fishing_group_size):
                start = fishing_group_start + 1 + (fishing_group_member * 2)
                level = rom[start]
                species = pokemon_slugs[rom[start + 1]]
                fishing_groups[map_id].append({
                    'version': version,
                    'location': map_slugs[map_id]['location'],
                    'area': map_slugs[map_id]['area'],
                    'method': 'super-rod',
                    'species': species,
                    'pokemon': species,
                    'level': level,
                    'chance': round((1 / fishing_group_size) * 100),
                    'conditions': None,
                    'note': None
                })
    elif version_group == 'yellow':
        fish_slot_chances = [40, 30, 20, 10]
        cursor = super_rod_offset
        while rom[cursor] != 0xFF:
            map_id = rom[cursor]
            cursor += 1
            fishing_groups[map_id] = []
            for chance in fish_slot_chances:
                species = pokemon_slugs[rom[cursor]]
                cursor += 1
                level = rom[cursor]
                cursor += 1
                fishing_groups[map_id].append({
                    'version': version,
                    'location': map_slugs[map_id]['location'],
                    'area': map_slugs[map_id]['area'],
                    'method': 'super-rod',
                    'species': species,
                    'pokemon': species,
                    'level': level,
                    'chance': chance,
                    'conditions': None,
                    'note': None
                })

    for map_id, map_info in map_slugs.items():
        start = grass_encounters_offset + (map_id * 2)
        pointer = rom[start:start + 2]
        cursor = gb.address_from_pointer(pointer, 0x03)

        # Walk/Surf Encounter data is in two chunks back-to-back.  If the first byte in the chunk is 0,
        # that method has no encounters.
        # e.g. No encounters ever = 00 00
        #      Only walk encounters = <rate> <10 encounter pairs> 00
        #      Only surf encounters = 00 <rate> <10 encounter pairs>
        #      Both methods encounters = <walk rate> <10 encounter pairs> <surf rate> <10 encounter pairs>
        for method in ['walk', 'surf']:
            map_rate = rom[cursor]
            if map_rate == 0x00:
                # No chance of encounters
                cursor += 1
                continue
            cursor += 1
            for chance in walk_slot_chances:
                if rom[cursor] == 0x00:
                    # End of encounter table
                    break
                level = rom[cursor]
                cursor += 1
                species_id = rom[cursor]
                cursor += 1
                out.append({
                    'version': version,
                    'location': map_info['location'],
                    'area': map_info['area'],
                    'method': method,
                    'species': pokemon_slugs[species_id],
                    'pokemon': pokemon_slugs[species_id],
                    'level': level,
                    'chance': chance,
                    'conditions': None,
                    'note': None
                })

        # Add fishing data if it exists
        if map_id in fishing_groups:
            # This is actually hardcoded in the game's code
            out.append({
                'version': version,
                'location': map_info['location'],
                'area': map_info['area'],
                'method': 'old-rod',
                'species': 'magikarp',
                'pokemon': 'magikarp',
                'level': 5,
                'chance': 100,
                'conditions': None,
                'note': None
            })
            out.append({
                'version': version,
                'location': map_info['location'],
                'area': map_info['area'],
                'method': 'good-rod',
                'species': 'goldeen',
                'pokemon': 'goldeen',
                'level': 10,
                'chance': 50,
                'conditions': None,
                'note': None
            })
            out.append({
                'version': version,
                'location': map_info['location'],
                'area': map_info['area'],
                'method': 'good-rod',
                'species': 'poliwag',
                'pokemon': 'poliwag',
                'level': 10,
                'chance': 50,
                'conditions': None,
                'note': None
            })
            out.extend(fishing_groups[map_id])

    return out


out_encounters = get_encounters()

# Write data
if args.write_pokemon:
    print('Writing Pokemon')
    for species_slug, species_data in progressbar.progressbar(out_pokemon.items()):
        yaml_path = os.path.join(args.out_pokemon, '{slug}.yaml'.format(slug=species_slug))
        try:
            with open(yaml_path, 'r') as pokemon_yaml:
                data = yaml.load(pokemon_yaml.read())
        except IOError:
            data = {}
        data.update(species_data)
        with open(yaml_path, 'w') as pokemon_yaml:
            yaml.dump(data, pokemon_yaml)

if args.write_pokemon_moves:
    print('Writing Pokemon moves')

    # Get existing data, removing those that have just been ripped.
    delete_learn_methods = [
        'level-up',
        'machine',
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
    skip_items = [
        '10f',
        '11f',
        '1f',
        '2f',
        '3f',
        '4f',
        '5f',
        '6f',
        '7f',
        '8f',
        '9f',
        'b1f',
        'b2f',
        'b4f',
        'boulderbadge',
        'cascadebadge',
        'coin',
        'earthbadge',
        'marshbadge',
        'oak-s-parcel',
        'pokedex',
        'rainbowbadge',
        's-s-ticket',
        'soulbadge',
        'thunderbadge',
        'volcanobadge',
    ]

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

if args.write_shops:
    print('Writing Shops')
    data = []
    with open(args.out_shops, 'r') as shops_csv:
        for row in progressbar.progressbar(csv.DictReader(shops_csv)):
            if row['version_group'] != version_group:
                data.append(row)

    data.extend(out_shops)
    with open(args.out_shops, 'w') as pokemon_moves_csv:
        writer = csv.DictWriter(pokemon_moves_csv, fieldnames=data[0].keys())
        writer.writeheader()
        for row in progressbar.progressbar(data):
            writer.writerow(row)

if args.write_encounters:
    print('Writing encounters')
    delete_encounter_methods = [
        'walk',
        'surf',
        'old-rod',
        'good-rod',
        'super-rod'
    ]
    data = []
    highest_id = 0
    with open(args.out_encounters, 'r') as encounters_csv:
        for row in progressbar.progressbar(csv.DictReader(encounters_csv)):
            if row['version'] != version or row['method'] not in delete_encounter_methods:
                data.append(row)
                highest_id = max(highest_id, int(row['id']))

    # Need to generate ids for our data; start with the highest id number in the existing data
    last_id = highest_id
    for encounter in out_encounters:
        encounter['id'] = last_id + 5
        last_id = encounter['id']
        data.append(encounter)
    with open(args.out_encounters, 'w') as encounters_csv:
        writer = csv.DictWriter(encounters_csv, fieldnames=data[0].keys())
        writer.writeheader()
        for row in progressbar.progressbar(data):
            writer.writerow(row)
