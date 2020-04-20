import argparse
from enum import Enum
import io
from io import BufferedReader
import os
import struct

from flags import Flags
import progressbar
from slugify import slugify

from inc import group_by_version_group, pokemon_text
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


out_moves = {}
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
