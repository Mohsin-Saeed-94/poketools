# Rip Gen 2 pokemon data

import argparse
import os

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
