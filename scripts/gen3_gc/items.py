from dataclasses import dataclass
from io import BufferedReader
from pathlib import Path
import re
import struct

import progressbar
from slugify import slugify

from inc.yaml import yaml
from .enums import item_name_changes, Version
from .strings import ColoStrings, get_string, XdStrings

out = {}
item_slugs = {}


def get_items(game_path: Path, version: Version, items_out_path: Path):
    out.clear()
    item_slugs.clear()

    print('Dumping items')

    _get_items(game_path, version)
    _pullup_data(version, items_out_path)

    return out, item_slugs


def _get_items(game_path: Path, version: Version):
    file_path = {
        Version.COLOSSEUM: game_path.joinpath(Path('../sys/main.dol')),
        Version.XD: game_path.joinpath(Path('common.fsys/common_rel.fdat')),
    }
    file_path = file_path[version]
    assert file_path.is_file()
    num_items = {
        Version.COLOSSEUM: 396,
        Version.XD: 442,
    }
    num_items = num_items[version]
    items_offset = {
        Version.COLOSSEUM: 0x360D10,
        Version.XD: 0x01FF0C,
    }
    items_offset = items_offset[version]
    item_length = 40
    name_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.COMMON_REL,
        Version.XD: XdStrings.StringTable.COMMON_REL,
    }
    name_table = name_table[version]
    flavor_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.POCKET_MENU_2,
        Version.XD: XdStrings.StringTable.POCKET_MENU_1,
    }
    flavor_table = flavor_table[version]

    pocket_map = {
        1: 'pokeballs',
        2: 'misc',
        3: 'berries',
        4: 'machines',
        5: 'key',
        6: 'cologne-case',
        7: 'disc-case',  # XD only
    }
    slug_overrides = {
        'gonzap-s-key': 'gonzaps-key',
        'king-s-rock': 'kings-rock',
        'oak-s-parcel': 'oaks-parcel',
        's-s-ticket': 'ss-ticket',
    }
    skip_items = [
        # Key items from the GBA games
        'pokeblock',
        'shoal-salt',
        'shoal-shell',
        'red-scarf',
        'blue-scarf',
        'pink-scarf',
        'green-scarf',
        'yellow-scarf',
        'mach-bike',
        'contest-pass',
        'wailmer-pail',
        'devon-goods',
        'soot-sack',
        'basement-key',
        'acro-bike',
        'pokeblock-case',
        'letter',
        'eon-ticket',
        'red-orb',
        'blue-orb',
        'scanner',
        'go-goggles',
        'rm-1-key',
        'rm-2-key',
        'rm-4-key',
        'rm-6-key',
        'storage-key',
        'root-fossil',
        'claw-fossil',
        'devon-scope',
        'magma-emblem',
        'old-sea-map',

        # Unused Pokeballs
        'safari-ball',
        'dive-ball',

        # Unobtainable items
        'battle-cd-51',
        'battle-cd-52',
        'battle-cd-53',
        'battle-cd-54',
        'battle-cd-55',
        'battle-cd-56',
        'battle-cd-57',
        'battle-cd-58',
        'battle-cd-59',
        'battle-cd-60',
        'safe-key',
    ]

    @dataclass()
    class Item:
        def __init__(self, data: bytes):
            data = struct.unpack('>B5xH8xII2xH12x', data)

            self.pocketId = data[0]
            self.buy = data[1]
            self.nameId = data[2]
            self.flavorId = data[3]
            self.position = data[4]

    with file_path.open('rb') as container:
        for item_id in range(1, num_items + 1):
            container.seek(items_offset + ((item_id - 1) * item_length))
            item = Item(container.read(item_length))
            if item.pocketId == 0 or item.nameId == 0:
                # Dummy item
                continue

            name = get_string(game_path, name_table, item.nameId)
            slug = slugify(name)
            if slug in slug_overrides:
                slug = slug_overrides[slug]
            if slug in skip_items:
                continue
            flavor_text = get_string(game_path, flavor_table, item.flavorId)

            if flavor_text == 'An item brought\nover from a faraway\nplace.':
                # This is the game's generic "Not from here" description.
                # It's used by things like pokeblock berries.
                continue

            # The DNA Samples all have the same name, but do different things.
            # The first one is the placeholder for unanalyzed samples.
            if slug == 'dna-sample':
                match = re.match(r'^(?P<pokemon>.+)\'s\sDNA\ssample\.$', flavor_text)
                if match:
                    pokemon_name = match.group('pokemon')
                    pokemon_slug = slugify(pokemon_name)
                    name = '{name} ({pokemon})'.format(name=name, pokemon=pokemon_name)
                    slug = '{slug}--{pokemon}'.format(slug=slug, pokemon=pokemon_slug)
                else:
                    slug = '{slug}--unanalyzed'.format(slug=slug)
                short_description = 'Part of the passcode in the []{location:shadow-pkmn-lab}.'
                description = '\n'.join([
                    'Part of the passcode in the []{location:shadow-pkmn-lab}.',
                    '',
                    'Three of these are scattered randomly about the lab.  They must',
                    'be analyzed using the DNA Analyzer first.'
                ])
            else:
                short_description = None
                description = None

            item_slugs[item_id] = slug

            out[slug] = {
                'name': name,
                'pocket': pocket_map[item.pocketId],
                'buy': None,
                'sell': None,
                'flavor_text': flavor_text,
            }
            if item.buy > 0:
                out[slug]['buy'] = item.buy
                out[slug]['sell'] = round(item.buy / 2)
            else:
                del out[slug]['buy']
                del out[slug]['sell']
            if short_description and description:
                out[slug]['short_description'] = short_description
                out[slug]['description'] = description


def _pullup_data(version: Version, items_out_path: Path):
    pullup_keys = [
        'category',
        'flags',
        'short_description',
        'description',
    ]
    print('Using existing data')
    for item_slug in progressbar.progressbar(out.keys()):
        if item_slug in item_name_changes:
            yaml_path = items_out_path.joinpath('{item}.yaml'.format(item=item_name_changes[item_slug]))
        else:
            yaml_path = items_out_path.joinpath('{item}.yaml'.format(item=item_slug))
        if not yaml_path.is_file():
            # Brand new item
            continue
        with yaml_path.open('r') as item_yaml:
            old_item_data = yaml.load(item_yaml.read())
            if version.value not in old_item_data:
                # If the name has changed, try the original name, as it may have been moved already.
                if item_slug in item_name_changes:
                    yaml_path = items_out_path.joinpath('{item}.yaml'.format(item=item_slug))
                    with yaml_path.open('r') as item_yaml:
                        old_item_data = yaml.load(item_yaml.read())
                else:
                    raise Exception(
                        'Item {item} has no data for version group {version_group}.'.format(
                            item=item_slug,
                            version_group=version.value))
            for key in pullup_keys:
                if key not in out[item_slug] and key in old_item_data[version.value]:
                    out[item_slug][key] = old_item_data[version.value][key]


def update_machines(game_path: Path, version: Version, out_items: dict, move_slugs: dict):
    # These games have no HMs.
    machine_table_offset = {
        Version.COLOSSEUM: 0x365018,
        Version.XD: 0x4023A0,
    }
    machine_table_offset = machine_table_offset[version]
    machine_table_entry_length = 8

    @dataclass()
    class MachineTableEntry:
        def __init__(self, data: bytes):
            data = struct.unpack('>?3xI', data)
            self.isHm = data[0]
            self.moveId = data[1]

    main_dol_path = (game_path.joinpath(Path('../sys/main.dol')))
    assert main_dol_path.is_file()
    main_dol: BufferedReader

    print('Dumping TM/HM data')
    with main_dol_path.open('rb') as main_dol:
        for number in range(1, 51):
            main_dol.seek(machine_table_offset + ((number - 1) * machine_table_entry_length))
            table_entry = MachineTableEntry(main_dol.read(machine_table_entry_length))
            item_slug = 'tm{number:02}'.format(number=number)
            move_slug = move_slugs[table_entry.moveId]
            out_items[item_slug]['machine'] = {
                'type': 'TM',
                'number': number,
                'move': move_slug,
            }

    return out_items


def write_items(out_data: dict, items_out_path: Path):
    print('Writing Items')
    used_version_groups = set()
    for item_slug, item_data in progressbar.progressbar(out_data.items()):
        yaml_path = items_out_path.joinpath('{slug}.yaml'.format(slug=item_slug))
        try:
            with yaml_path.open('r') as item_yaml:
                data = yaml.load(item_yaml.read())
        except IOError:
            data = {}
        data.update(item_data)
        used_version_groups.update(item_data.keys())
        with yaml_path.open('w') as item_yaml:
            yaml.dump(data, item_yaml)

    # Remove this version group's data from extra files
    for yaml_path in progressbar.progressbar(items_out_path.iterdir()):
        if yaml_path.suffix != '.yaml':
            continue
        if yaml_path.stem in out_data.keys():
            continue

        # This version group doesn't contain the item in this file.
        # Remove it if present.
        with yaml_path.open('r') as item_yaml:
            data = yaml.load(item_yaml.read())
        changed = False
        for check_version_group in used_version_groups:
            if check_version_group in data:
                del data[check_version_group]
                changed = True
        if changed:
            with yaml_path.open('w') as item_yaml:
                yaml.dump(data, item_yaml)
