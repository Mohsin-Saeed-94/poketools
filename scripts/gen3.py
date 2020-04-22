import argparse
from enum import Enum
import io
from io import BufferedReader
import os
import struct

from flags import Flags
import progressbar
from slugify import slugify

from inc import gba, group_by_version_group, pokemon_text
from inc.yaml import yaml

pokemon_text.register()


class Version(Enum):
    RUBY = 'ruby'
    SAPPHIRE = 'sapphire'
    EMERALD = 'emerald'
    FIRERED = 'firered'
    LEAFGREEN = 'leafgreen'


# Get config
argparser = argparse.ArgumentParser(description='Load Gen 3 data.  (R/S uses Rev 1.2 ROMs)')
argparser.add_argument('--rom', action='append', type=argparse.FileType('rb'), required=True, help='ROM File path')
argparser.add_argument('--version', action='append', type=str, choices=[version.value for version in Version],
                       help='Version slug to dump')
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


class VersionGroup(Enum):
    RUBY_SAPPHIRE = 'ruby-sapphire'
    EMERALD = 'emerald'
    FIRERED_LEAFGREEN = 'firered-leafgreen'


# What version is this?
versionmap = {
    'AXVE': Version.RUBY,
    'AXPE': Version.SAPPHIRE,
    'BPEE': Version.EMERALD,
    'BPRE': Version.FIRERED,
    'BPGE': Version.LEAFGREEN,
}
versiongroupmap = {
    Version.RUBY: VersionGroup.RUBY_SAPPHIRE,
    Version.SAPPHIRE: VersionGroup.RUBY_SAPPHIRE,
    Version.EMERALD: VersionGroup.EMERALD,
    Version.FIRERED: VersionGroup.FIRERED_LEAFGREEN,
    Version.LEAFGREEN: VersionGroup.FIRERED_LEAFGREEN,
}

type_map = {
    0x00: 'normal',
    0x01: 'fighting',
    0x02: 'flying',
    0x03: 'poison',
    0x04: 'ground',
    0x05: 'rock',
    0x06: 'bug',
    0x07: 'ghost',
    0x08: 'steel',
    0x09: 'unknown',
    0x0A: 'fire',
    0x0B: 'water',
    0x0C: 'grass',
    0x0D: 'electric',
    0x0E: 'psychic',
    0x0F: 'ice',
    0x10: 'dragon',
    0x11: 'dark',
}

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


# TODO: Contest types
def get_moves(rom: BufferedReader, version_group: VersionGroup, version: Version):
    num_moves = 355
    out = {}
    move_slugs = {}

    print('Dumping moves')

    def _get_move_names():
        name_length = 13
        names_offset = {
            Version.RUBY: 0x1F8338,
            Version.SAPPHIRE: 0x1F82C8,
            Version.EMERALD: 0x31977C,
            Version.FIRERED: 0x247094,
            Version.LEAFGREEN: 0x247070
        }
        names_offset = names_offset[version]

        # This will skip the dummy move 0
        for move_id in range(1, num_moves):
            rom.seek(names_offset + (move_id * name_length))
            name = bytearray()
            while rom.peek(1)[0] != 0xFF and len(name) < name_length:
                name.append(rom.read(1)[0])
            name = name.decode('pokemon_gen3')
            slug = slugify(name)
            move_slugs[move_id] = slug
            out[slug] = {
                'name': name,
            }

    def _get_move_data():
        target_map = {
            0: 'selected-pokemon',
            1 << 0: 'specific-move',
            1 << 2: 'random-opponent',
            1 << 3: 'all-opponents',
            1 << 4: 'user',
            1 << 5: 'all-other-pokemon',
            1 << 6: 'opponents-field',
        }

        class MoveFlags(Flags):
            CONTACT = 1 << 0, 'contact'
            PROTECT = 1 << 1, 'protect'
            REFLECTABLE = 1 << 2, 'reflectable'
            SNATCH = 1 << 3, 'snatch'
            MIRROR = 1 << 4, 'mirror'
            KINGS_ROCK = 1 << 5, 'kings-rock'

        data_length = 9
        data_length_aligned = 12
        data_offset = {
            Version.RUBY: 0x1FB144,
            Version.SAPPHIRE: 0x1FB0D4,
            Version.EMERALD: 0x31C898,
            Version.FIRERED: 0x250C04,
            Version.LEAFGREEN: 0x250BE0,
        }
        data_offset = data_offset[version]

        class MoveStats:
            def __init__(self, data: bytes):
                data = struct.unpack('<BBBBBBBbB', data)
                self.effectId = data[0] + 1
                self.power = data[1]
                self.type = type_map[data[2]]
                self.accuracy = data[3]
                self.pp = data[4]
                self.effectChance = data[5]
                self.target = target_map[data[6]]
                self.priority = data[7]
                self.flags = [flag.data for flag in MoveFlags(data[8])]

                pass

        for move_id, move_slug in move_slugs.items():
            rom.seek(data_offset + (move_id * data_length_aligned))
            data = rom.read(data_length)
            move_stats = MoveStats(data)
            out[move_slug].update({
                'power': move_stats.power,
                'type': move_stats.type,
                'accuracy': move_stats.accuracy,
                'pp': move_stats.pp,
                'effect': move_stats.effectId,
                'target': move_stats.target,
                'priority': move_stats.priority,
            })
            if 0 < move_stats.effectChance < 100:
                out[move_slug]['effect_chance'] = move_stats.effectChance
            if out[move_slug]['power'] == 0:
                del out[move_slug]['power']
            if out[move_slug]['accuracy'] == 0:
                del out[move_slug]['accuracy']
            if len(move_stats.flags) > 0:
                out[move_slug]['flags'] = move_stats.flags

    def _pullup_data():
        pullup_keys = [
            'crit_rate_bonus',
            'drain',
            'flinch_chance',
            'ailment',
            'ailment_chance',
            'recoil',
            'healing',
            'categories',
            'hits',
            'turns',
            'stat_changes',
            'stat_change_chance',
        ]
        print('Using existing data')
        for move_slug in progressbar.progressbar(move_slugs.values()):
            if move_slug in move_name_changes:
                yaml_path = os.path.join(args.out_moves, '{move}.yaml'.format(move=move_name_changes[move_slug]))
            else:
                yaml_path = os.path.join(args.out_moves, '{move}.yaml'.format(move=move_slug))
            with open(yaml_path, 'r') as move_yaml:
                old_move_data = yaml.load(move_yaml.read())
                if version_group.value not in old_move_data:
                    # If the name has changed, try the original name, as it may have been moved already.
                    if move_slug in move_name_changes:
                        yaml_path = os.path.join(args.out_moves, '{move}.yaml'.format(move=move_slug))
                        with open(yaml_path, 'r') as move_yaml:
                            old_move_data = yaml.load(move_yaml.read())
                    else:
                        raise Exception(
                            'Move {move} has no data for version group {version_group}.'.format(
                                move=move_slug,
                                version_group=version_group.value))
                for key in pullup_keys:
                    if key in old_move_data[version_group.value]:
                        out[move_slug][key] = old_move_data[version_group.value][key]

    def _get_flavor():
        flavor_offset = {
            Version.RUBY: 0x3BC69C,
            Version.SAPPHIRE: 0x3BC6F8,
            Version.EMERALD: 0x6181c1,
            Version.FIRERED: 0x482834,
            Version.LEAFGREEN: 0x482110,
        }
        flavor_offset = flavor_offset[version]
        rom.seek(flavor_offset)
        for move_slug in move_slugs.values():
            flavor_text = bytearray()
            while rom.peek(1)[0] != 0xFF:
                flavor_text.append(rom.read(1)[0])
            flavor_text = flavor_text.decode('pokemon_gen3')
            out[move_slug]['flavor_text'] = flavor_text
            rom.seek(1, io.SEEK_CUR)

    _get_move_names()
    _get_move_data()
    _pullup_data()
    _get_flavor()

    return out, move_slugs


def write_moves(out):
    print('Writing Moves')
    used_version_groups = set()
    for move_slug, move_data in progressbar.progressbar(out.items()):
        yaml_path = os.path.join(args.out_moves, '{slug}.yaml'.format(slug=move_slug))
        try:
            with open(yaml_path, 'r') as move_yaml:
                data = yaml.load(move_yaml.read())
        except IOError:
            data = {}
        data.update(move_data)
        used_version_groups.update(move_data.keys())
        with open(yaml_path, 'w') as move_yaml:
            yaml.dump(data, move_yaml)

    # Remove this version group's data from the new name file
    for old_name, new_name in move_name_changes.items():
        yaml_path = os.path.join(args.out_moves, '{slug}.yaml'.format(slug=new_name))
        with open(yaml_path, 'r') as move_yaml:
            data = yaml.load(move_yaml.read())
        changed = False
        for check_version_group in used_version_groups:
            try:
                del data[check_version_group]
                changed = True
            except KeyError:
                # No need to re-write this file
                continue
        if changed:
            with open(yaml_path, 'w') as move_yaml:
                yaml.dump(data, move_yaml)


item_name_changes = {
    'blackglasses': 'black-glasses',
    'nevermeltice': 'never-melt-ice',
    'parlyz-heal': 'paralyze-heal',
    'silverpowder': 'silver-powder',
    'x-defend': 'x-defense',
    'x-special': 'x-sp-atk',
    'thunderstone': 'thunder-stone',
    'tinymushroom': 'tiny-mushroom',
    'twistedspoon': 'twisted-spoon',
}


def get_items(rom: BufferedReader, version_group: VersionGroup, version: Version):
    num_items = {
        VersionGroup.RUBY_SAPPHIRE: 349,
        VersionGroup.EMERALD: 377,
        VersionGroup.FIRERED_LEAFGREEN: 375,
    }
    num_items = num_items[version_group]

    out = {}
    item_slugs = {}

    print('Dumping items')

    def _get_item_data():
        slug_overrides = {
            'king-s-rock': 'kings-rock',
            'oak-s-parcel': 'oaks-parcel',
            's-s-ticket': 'ss-ticket',
        }
        # This is the start of every game having every key item from the games before it, so lots of exclusions.
        skip_items = {
            VersionGroup.RUBY_SAPPHIRE: ['pokeblock'],
            VersionGroup.EMERALD: [
                'pokeblock',
                'contest-pass',
                'oaks-parcel',
                'poke-flute',
                'secret-key',
                'bike-voucher',
                'gold-teeth',
                'old-amber',
                'card-key',
                'lift-key',
                'helix-fossil',
                'dome-fossil',
                'silph-scope',
                'bicycle',
                'town-map',
                'vs-seeker',
                'fame-checker',
                'tm-case',
                'berry-pouch',
                'teachy-tv',
                'tri-pass',
                'rainbow-pass',
                'tea',
                'powder-jar',
                'ruby',
                'sapphire',
            ],
            VersionGroup.FIRERED_LEAFGREEN: [
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
                'meteorite',
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
            ],
        }
        data_offset = {
            Version.RUBY: 0x3C5580,
            Version.SAPPHIRE: 0x3C55DC,
            Version.EMERALD: 0x5839A0,
            Version.FIRERED: 0x3DB028,
            Version.LEAFGREEN: 0x3DAE64,
        }
        data_offset = data_offset[version]
        data_length = 38
        data_length_aligned = 44
        if version_group == VersionGroup.FIRERED_LEAFGREEN:
            pocket_map = {
                0x01: 'misc',
                0x02: 'key',
                0x03: 'pokeballs',
                0x04: 'machines',
                0x05: 'berries',
            }
        else:
            pocket_map = {
                0x01: 'misc',
                0x02: 'pokeballs',
                0x03: 'machines',
                0x04: 'berries',
                0x05: 'key',
            }

        class ItemData:
            def __init__(self, data: bytes):
                data = struct.unpack('<14sHHBB4sB?BB4sB4sB', data)
                self.name = data[0].decode('pokemon_gen3').strip()
                self.itemId = data[1]
                self.price = data[2]
                self.holdEffect = data[3]
                self.holdEffectParam = data[4]
                self.descriptionPointer = data[5]
                self.importance = data[6]
                self.exitsBagOnUse = data[7]
                self.pocket = pocket_map[data[8]]
                self.type = data[9]
                self.battleUsage = data[11]
                self.secondaryId = data[13]

        for item_id in range(0, num_items):
            rom.seek(data_offset + (item_id * data_length_aligned))
            data = rom.read(data_length)
            item_stats = ItemData(data)
            if item_stats.itemId == 0:
                # Dummy item
                continue
            slug = slugify(item_stats.name)
            if slug in slug_overrides:
                slug = slug_overrides[slug]
            if slug in skip_items[version_group]:
                continue
            item_slugs[item_id] = slug
            out[slug] = {
                'name': item_stats.name,
                'category': None,
                'pocket': item_stats.pocket,
                'flags': [],
                'icon': '{slug}.png'.format(slug=slug)
            }
            if item_stats.price > 0:
                out[slug].update({
                    'buy': item_stats.price,
                    'sell': item_stats.price // 2,
                })
            rom.seek(gba.address_from_pointer(item_stats.descriptionPointer))
            description = bytearray()
            while rom.peek(1)[0] != 0xFF:
                description.append(rom.read(1)[0])
            description = description.decode('pokemon_gen3')
            out[slug]['flavor_text'] = description

    def _pullup_data():
        pullup_keys = [
            'category',
            'flags',
            'short_description',
            'description',
        ]
        print('Using existing data')
        for item_slug in progressbar.progressbar(item_slugs.values()):
            if item_slug in item_name_changes:
                yaml_path = os.path.join(args.out_items, '{item}.yaml'.format(item=item_name_changes[item_slug]))
            else:
                yaml_path = os.path.join(args.out_items, '{item}.yaml'.format(item=item_slug))
            with open(yaml_path, 'r') as item_yaml:
                old_item_data = yaml.load(item_yaml.read())
                if version_group.value not in old_item_data:
                    # If the name has changed, try the original name, as it may have been moved already.
                    if item_slug in item_name_changes:
                        yaml_path = os.path.join(args.out_items, '{item}.yaml'.format(item=item_slug))
                        with open(yaml_path, 'r') as item_yaml:
                            old_item_data = yaml.load(item_yaml.read())
                    else:
                        raise Exception(
                            'Item {item} has no data for version group {version_group}.'.format(
                                item=item_slug,
                                version_group=version_group.value))
                for key in pullup_keys:
                    if key in old_item_data[version_group.value]:
                        out[item_slug][key] = old_item_data[version_group.value][key]

    _get_item_data()
    _pullup_data()

    return out, item_slugs


def update_machines(rom: BufferedReader, version: Version, items: dict, move_slugs: dict):
    machine_count = {
        'TM': 50,
        'HM': 8,
    }
    machine_table_offset = {
        Version.RUBY: 0x37651C,
        Version.SAPPHIRE: 0x3764AC,
        Version.EMERALD: 0x615B94,
        Version.FIRERED: 0x45A5A4,
        Version.LEAFGREEN: 0x459FC4,
    }
    machine_table_offset = machine_table_offset[version]

    print('Dumping TM/HM data')

    def _update_machine_item(type: str, number: int, move_id: int):
        item_slug = '{type}{number:02}'.format(type=type.lower(), number=number)
        move_slug = move_slugs[move_id]
        items[item_slug]['machine'] = {
            'type': type.upper(),
            'number': number,
            'move': move_slug,
        }

    rom.seek(machine_table_offset)
    for machine_type, num_machines in machine_count.items():
        for machine_number in range(1, num_machines + 1):
            move_id = int.from_bytes(rom.read(2), byteorder='little')
            _update_machine_item(machine_type, machine_number, move_id)

    return items


def update_berries(rom: BufferedReader, version_group, version: Version, items: dict):
    num_berries = 42
    data_offset = {
        Version.RUBY: 0x3CD2E8,
        Version.SAPPHIRE: 0x3CD344,
        Version.EMERALD: 0x58A670,
        Version.FIRERED: 0x3DF7E8,
        Version.LEAFGREEN: 0x3DF624,
    }
    data_offset = data_offset[version]
    data_length = 27
    data_length_aligned = 28

    firmness_map = {
        0x01: 'very-soft',
        0x02: 'soft',
        0x03: 'hard',
        0x04: 'very-hard',
        0x05: 'super-hard',
    }

    print('Dumping Berry data')

    class BerryData:
        def __init__(self, data: bytes):
            data = struct.unpack('<7sBHBB4s4sBBBBBBB', data)
            self.name = data[0].decode('pokemon_gen3')
            self.firmness = firmness_map[data[1]]
            self.size = data[2]
            self.harvestMax = data[3]
            self.harvestMin = data[4]
            self.descriptionPointers = [data[5], data[6]]
            self.growthTime = data[7]
            self.flavors = {
                'spicy': data[8],
                'dry': data[9],
                'sweet': data[10],
                'bitter': data[11],
                'sour': data[12],
            }
            self.smoothness = data[13]

    for berry_number in range(1, num_berries + 1):
        rom.seek(data_offset + ((berry_number - 1) * data_length_aligned))
        data = rom.read(data_length)
        berry = BerryData(data)
        slug = slugify(berry.name)
        item_slug = '{slug}-berry'.format(slug=slug)
        if berry.harvestMin == berry.harvestMax:
            harvest = str(berry.harvestMin)
        else:
            harvest = '{min}-{max}'.format(min=berry.harvestMin, max=berry.harvestMax)
        items[item_slug]['berry'] = {
            'number': berry_number,
            'firmness': berry.firmness,
            'size': berry.size,
            'growth_time': berry.growthTime,
            'smoothness': berry.smoothness,
            'flavors': berry.flavors,
            'harvest': harvest,
        }
        if version_group != VersionGroup.FIRERED_LEAFGREEN:
            description = []
            for desc_pointer in berry.descriptionPointers:
                line = bytearray()
                rom.seek(gba.address_from_pointer(desc_pointer))
                while rom.peek(1)[0] != 0xFF:
                    line.append(rom.read(1)[0])
                line = line.decode('pokemon_gen3')
                description.append(line)
            items[item_slug]['berry']['flavor_text'] = '\n'.join(description)

    return items


def write_items(out):
    print('Writing Items')
    used_version_groups = set()
    for item_slug, item_data in progressbar.progressbar(out.items()):
        yaml_path = os.path.join(args.out_items, '{slug}.yaml'.format(slug=item_slug))
        try:
            with open(yaml_path, 'r') as item_yaml:
                data = yaml.load(item_yaml.read())
        except IOError:
            data = {}
        data.update(item_data)
        used_version_groups.update(item_data.keys())
        with open(yaml_path, 'w') as item_yaml:
            yaml.dump(data, item_yaml)

    # Remove this version group's data from the new name file
    for old_name, new_name in item_name_changes.items():
        yaml_path = os.path.join(args.out_items, '{slug}.yaml'.format(slug=new_name))
        with open(yaml_path, 'r') as item_yaml:
            data = yaml.load(item_yaml.read())
        changed = False
        for check_version_group in used_version_groups:
            try:
                del data[check_version_group]
                changed = True
            except KeyError:
                # No need to re-write this file
                continue
        if changed:
            with open(yaml_path, 'w') as item_yaml:
                yaml.dump(data, item_yaml)


out_moves = {}
out_items = {}
dumped_versions = []
dump_rom: BufferedReader
for dump_rom in args.rom:
    dump_rom.seek(0xAC)
    dump_version = dump_rom.read(4)
    dump_version = dump_version.decode('ascii')
    dump_version = versionmap[dump_version]
    if len(args.version) > 0 and dump_version.value not in args.version:
        # Skip this version
        continue
    dump_version_group = versiongroupmap[dump_version]
    print('Using version group {version_group}'.format(version_group=dump_version_group.value))
    print('Using version {version}'.format(version=dump_version.value))

    vg_moves, vg_move_slugs = get_moves(dump_rom, dump_version_group, dump_version)
    out_moves = group_by_version_group(dump_version_group.value, vg_moves, out_moves)
    vg_items, vg_item_slugs = get_items(dump_rom, dump_version_group, dump_version)
    vg_items = update_machines(dump_rom, dump_version, vg_items, vg_move_slugs)
    vg_items = update_berries(dump_rom, dump_version_group, dump_version, vg_items)
    out_items = group_by_version_group(dump_version_group.value, vg_items, out_items)

    dumped_versions.append(dump_version.value)

if len(dumped_versions) < len(args.version):
    # Didn't dump all requested versions
    missing_versions = []
    for requested_version in args.version:
        if requested_version not in dumped_versions:
            missing_versions.append(requested_version)
    print('Could not dump these versions because the ROMs were not available: {roms}'.format(
        roms=', '.join(missing_versions)))
else:
    if args.write_moves:
        write_moves(out_moves)
    if args.write_items:
        write_items(out_items)
