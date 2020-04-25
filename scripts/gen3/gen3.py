import argparse
import csv
from dataclasses import dataclass
import io
from io import BufferedReader
import os
import struct
from typing import Any, Dict, List

from flags import Flags
import progressbar
from slugify import slugify

from gen3 import script
from gen3.enums import Version, VersionGroup
from inc import gba, group_by_version_group, pokemon_text
from inc.yaml import yaml

pokemon_text.register()

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


def get_moves(rom: BufferedReader, version_group: VersionGroup, version: Version):
    num_moves = 355
    num_contest_effects = 48
    out_moves = {}
    out_contest_effects = {}
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
            out_moves[slug] = {
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

        @dataclass()
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
            out_moves[move_slug].update({
                'power': move_stats.power,
                'type': move_stats.type,
                'accuracy': move_stats.accuracy,
                'pp': move_stats.pp,
                'effect': move_stats.effectId,
                'target': move_stats.target,
                'priority': move_stats.priority,
            })
            if 0 < move_stats.effectChance < 100:
                out_moves[move_slug]['effect_chance'] = move_stats.effectChance
            if out_moves[move_slug]['power'] == 0:
                del out_moves[move_slug]['power']
            if out_moves[move_slug]['accuracy'] == 0:
                del out_moves[move_slug]['accuracy']
            if len(move_stats.flags) > 0:
                out_moves[move_slug]['flags'] = move_stats.flags

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
                        out_moves[move_slug][key] = old_move_data[version_group.value][key]

    def _get_contest_move_data():
        contest_type_map = {
            0x00: 'cool',
            0x01: 'beauty',
            0x02: 'cute',
            0x03: 'smart',
            0x04: 'tough',
        }
        data_offset = {
            Version.RUBY: 0x3CF5B0,
            Version.SAPPHIRE: 0x3CF60C,
            Version.EMERALD: 0x58C2B4,
        }
        if version not in data_offset:
            # Skip versions without contests
            return
        data_offset = data_offset[version]
        data_length = 7
        data_length_aligned = 8

        @dataclass
        class ContestMove:
            def __init__(self, data: bytes):
                data = struct.unpack('<BBB4B', data)
                self.effectId = data[0] + 1
                self.contestType = contest_type_map[data[1] & 0x07]
                self.comboStarterId = data[2]
                if self.comboStarterId == 0:
                    self.comboStarterId = None
                self.comboMoves = []
                for combo_move in data[3:7]:
                    if combo_move != 0:
                        self.comboMoves.append(combo_move)

        # First pass - contest base data
        move_combo_starters = {}
        move_combo_members = {}
        contest_data = {}
        for move_id, move_slug in move_slugs.items():
            rom.seek(data_offset + (move_id * data_length_aligned))
            data = rom.read(data_length)
            contest_move = ContestMove(data)
            contest_data[move_slug] = contest_move

            out_moves[move_slug].update({
                'contest_type': contest_move.contestType,
                'contest_effect': contest_move.effectId,
            })
            if contest_move.comboStarterId:
                move_combo_starters[contest_move.comboStarterId] = move_slug
            for combo_move in contest_move.comboMoves:
                if combo_move not in move_combo_members:
                    move_combo_members[combo_move] = []
                move_combo_members[combo_move].append(move_slug)

        # Second pass - assemble the combos
        for move_slug, contest_move in contest_data.items():
            if contest_move.comboStarterId:
                # This is the "before move"
                if 'contest_use_before' not in out_moves[move_slug]:
                    out_moves[move_slug]['contest_use_before'] = []
                out_moves[move_slug]['contest_use_before'].extend(move_combo_members[contest_move.comboStarterId])
            for combo_move in contest_move.comboMoves:
                # This is an "after move"
                if 'contest_use_after' not in out_moves[move_slug]:
                    out_moves[move_slug]['contest_use_after'] = []
                out_moves[move_slug]['contest_use_after'].append(move_combo_starters[combo_move])

    def _get_contest_effect_data():
        effect_type_map = {
            0x00: 'constant-appeal',
            0x01: 'prevent-startle',
            0x02: 'startles-last-appealer',
            0x03: 'startles-previous-appealers',
            0x04: 'affects-other-appealers',
            0x05: 'special',
            0x06: 'change-order',
        }
        data_offset = {
            Version.RUBY: 0x3D00C8,
            Version.SAPPHIRE: 0x3D0124,
            Version.EMERALD: 0x58CDCC,
        }
        if version not in data_offset:
            # Skip versions without contests
            return
        data_offset = data_offset[version]
        data_length = 3
        data_length_aligned = 4

        @dataclass()
        class ContestEffect:
            def __init__(self, data: bytes):
                data = struct.unpack('<BBB', data)
                self.effectType = effect_type_map[data[0]]
                self.appeal = data[1] // 10
                self.jam = data[2] // 10

        for effect_id in range(1, num_contest_effects + 1):
            rom.seek(data_offset + ((effect_id - 1) * data_length_aligned))
            data = rom.read(data_length)
            contest_effect = ContestEffect(data)
            out_contest_effects[effect_id] = {
                'category': contest_effect.effectType,
                'appeal': contest_effect.appeal,
                'jam': contest_effect.jam,
            }

    def _get_contest_flavor():
        flavor_offset = {
            Version.RUBY: 0x3CA508,
            Version.SAPPHIRE: 0x3CA564,
            Version.EMERALD: 0x27CB82,
        }
        if version not in flavor_offset:
            # Skip versions without contests
            return
        data_offset = flavor_offset[version]

        rom.seek(data_offset)
        for effect_id in range(1, num_contest_effects + 1):
            flavor_text = bytearray()
            while rom.peek(1)[0] != 0xFF:
                flavor_text.append(rom.read(1)[0])
            rom.seek(1, io.SEEK_CUR)
            flavor_text = flavor_text.decode('pokemon_gen3')
            out_contest_effects[effect_id]['flavor_text'] = flavor_text

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
            out_moves[move_slug]['flavor_text'] = flavor_text
            rom.seek(1, io.SEEK_CUR)

    _get_move_names()
    _get_move_data()
    _pullup_data()
    _get_contest_move_data()
    _get_contest_effect_data()
    _get_contest_flavor()
    _get_flavor()

    return out_moves, out_contest_effects, move_slugs


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


def write_contest_effects(out):
    print('Writing Contest Effects')
    for effect_id, effect_data in progressbar.progressbar(out.items()):
        yaml_path = os.path.join(args.out_contest_effects, '{slug}.yaml'.format(slug=effect_id))
        try:
            with open(yaml_path, 'r') as effect_yaml:
                data = yaml.load(effect_yaml.read())
        except IOError:
            data = {}
        data.update(effect_data)
        with open(yaml_path, 'w') as effect_yaml:
            yaml.dump(data, effect_yaml)


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

        @dataclass()
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

    @dataclass()
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


def _has_pointer(pointer: bytes):
    if int.from_bytes(pointer, byteorder='big') > 0:
        return pointer
    else:
        return None


@dataclass()
class MapLayout:
    length = 24

    def __init__(self, data: bytes):
        data = struct.unpack('<ii4s4s4s4s', data)
        self.width = data[0]
        self.height = data[1]
        self.borderPointer = _has_pointer(data[2])
        self.blocksPointer = _has_pointer(data[3])
        self.mapBlocks = None
        self.primaryTilesetPointer = _has_pointer(data[4])
        self.primaryTileset = None
        self.secondaryTilesetPointer = _has_pointer(data[5])
        self.secondaryTileset = None


@dataclass()
class Tileset:
    length = 24

    def __init__(self, data: bytes):
        data = struct.unpack('<??2x4s4s4s4s4s', data)
        self.compressed = data[0]
        self.secondary = data[1]
        self.tilesPointer = _has_pointer(data[2])
        self.tilePalettesPointer = _has_pointer(data[3])
        self.metatilesPointer = _has_pointer(data[4])
        self.metatileAttributesPointer = _has_pointer(data[5])
        self.callbackPointer = _has_pointer(data[6])


@dataclass()
class MapEventsHeader:
    length = 20

    def __init__(self, data: bytes):
        data = struct.unpack('<BBBB4s4s4s4s', data)
        self.numObjectEvents = data[0]
        self.numWarps = data[1]
        self.numCoordEvents = data[2]
        self.numBgEvents = data[3]
        self.objectEventsPointer = _has_pointer(data[4])
        self.objectEvents: Dict[int, ObjectEvent] = {}
        self.warpsPointer = _has_pointer(data[5])
        self.coordEventsPointer = _has_pointer(data[6])
        self.bgEventsPointer = _has_pointer(data[7])


@dataclass()
class ObjectEvent:
    length = 24

    def __init__(self, data: bytes):
        data = struct.unpack('<BBBxhhBBBxHH4sH2x', data)
        self.eventId = data[0]
        self.spriteId = data[1]
        self.replacementId = data[2]
        self.x = data[3]
        self.y = data[4]
        self.elevation = data[5]
        self.movementType = data[6]
        self.movementRangeX = data[7] & 0x0F
        self.movementRangeY = (data[7] & 0xF0) >> 4
        self.trainerType = data[8]
        # Trainer sight range and berry tree ID are stored in the same place
        self.sightRange = data[9]
        self.berryTreeId = data[9]
        self.scriptPointer = _has_pointer(data[10])
        self.eventFlagId = data[11]


@dataclass()
class MapHeader:
    length = 28

    def __init__(self, data: bytes):
        data = struct.unpack('<4s4s4s4sHHB?BBx?BB', data)
        self.layoutPointer = _has_pointer(data[0])
        self.layout = None
        self.eventsPointer = _has_pointer(data[1])
        self.events = None
        self.scriptsPointer = _has_pointer(data[2])
        self.scripts = None
        self.connectionsPointer = _has_pointer(data[3])
        self.connections = None
        self.musicId = data[4]
        self.layoutId = data[5]
        self.mapSectionId = data[6]
        self.flashRequired = data[7]
        self.weatherId = data[8]
        self.mapTypeId = data[9]
        self.escapeRope = data[10]
        self.flags = data[11]
        self.battleType = data[12]


def _get_map(rom: BufferedReader, version_group: VersionGroup, version, group_id: int, map_id: int):
    group_pointer_offset = {
        Version.RUBY: 0x3085A0,
        Version.SAPPHIRE: 0x307F08,
        Version.EMERALD: 0x486578,
        Version.FIRERED: 0x3526A8,
        Version.LEAFGREEN: 0x352688,
    }
    group_pointer_offset = group_pointer_offset[version]

    # Follow the pointers to the header
    old_position = rom.tell()
    rom.seek(group_pointer_offset + (group_id * 4))
    map_group_pointer = rom.read(4)
    map_group_offset = gba.address_from_pointer(map_group_pointer)
    rom.seek(map_group_offset + (map_id * 4))
    map_header_pointer = rom.read(4)
    rom.seek(gba.address_from_pointer(map_header_pointer))

    game_map = MapHeader(rom.read(MapHeader.length))

    if game_map.layoutPointer:
        rom.seek(gba.address_from_pointer(game_map.layoutPointer))
        game_map.layout = MapLayout(rom.read(MapLayout.length))

        if game_map.layout.primaryTilesetPointer:
            rom.seek(gba.address_from_pointer(game_map.layout.primaryTilesetPointer))
            game_map.layout.primaryTileset = Tileset(rom.read(Tileset.length))
        if game_map.layout.secondaryTilesetPointer:
            rom.seek(gba.address_from_pointer(game_map.layout.secondaryTilesetPointer))
            game_map.layout.secondaryTileset = Tileset(rom.read(Tileset.length))

    if game_map.eventsPointer:
        rom.seek(gba.address_from_pointer(game_map.eventsPointer))
        game_map.events = MapEventsHeader(rom.read(MapEventsHeader.length))
        if game_map.events.objectEventsPointer:
            rom.seek(gba.address_from_pointer(game_map.events.objectEventsPointer))
            for i in range(game_map.events.numObjectEvents):
                object_event = ObjectEvent(rom.read(ObjectEvent.length))
                game_map.events.objectEvents[object_event.eventId] = object_event

    rom.seek(old_position)

    return game_map


def get_shops(rom: BufferedReader, version_group: VersionGroup, version: Version, item_slugs: dict, items: dict):
    if version_group == VersionGroup.RUBY_SAPPHIRE:
        from .rs_maps import map_slugs
        from .script.rs_commands import ScriptCommand
    elif version_group == VersionGroup.EMERALD:
        from .e_maps import map_slugs
        from .script.e_commands import ScriptCommand
    else:
        from .frlg_maps import map_slugs
        from .script.frlg_commands import ScriptCommand

    # location -> area -> shop identifier -> shop data
    shops: Dict[str, Dict[str, Dict[str, dict]]] = {}
    shop_items: List[Dict[str, Any]] = []

    print('Searching map scripts for shops')

    # State variables used when a Pokemart script has been triggered.
    current_location = None
    current_area = None
    current_event = None
    shop_id_counter = {}

    def _parse_pokemart(pointer: bytes, command_rom: BufferedReader):
        # Store the shop info
        if current_location not in shops:
            shops[current_location] = {}
        if current_area not in shops[current_location]:
            shops[current_location][current_area] = {}

        # Differentiate between several pokemart commands in the same event
        counter_id = '{location}_{area}_{event}'.format(location=current_location, area=current_area,
                                                        event=current_event.eventId)
        if counter_id not in shop_id_counter:
            shop_id_counter[counter_id] = 0
        else:
            shop_id_counter[counter_id] += 1
        shop_id = shop_id_counter[counter_id]
        shop_identifier = 'event-{event}-shop-{shop}'.format(event=current_event.eventId, shop=shop_id)

        shops[current_location][current_area][shop_identifier] = {
            'name': shop_identifier,
        }
        if len(shops[current_location][current_area]) == 1:
            shops[current_location][current_area][shop_identifier]['default'] = True

        # Store the shop's inventory.  The shop name will need manual tuning.
        old_position = command_rom.tell()
        command_rom.seek(gba.address_from_pointer(pointer))
        while command_rom.peek(1)[0] != 0:
            item_id = int.from_bytes(command_rom.read(2), 'little')
            shop_items.append({
                'version_group': version_group.value,
                'location': current_location,
                'area': current_area,
                'shop': shop_identifier,
                'item': item_slugs[item_id],
                'buy': items[item_slugs[item_id]]['buy']
            })
        command_rom.seek(old_position)

    # Parse all map scripts, handling pokemart calls
    progress = progressbar.ProgressBar(max_value=sum([len(maps) for maps in map_slugs.values()]))
    i = 0
    for map_group_id, maps in map_slugs.items():
        for map_id, map_info in maps.items():
            current_location = map_info['location']
            current_area = map_info['area']
            game_map = _get_map(rom, version_group, version, map_group_id, map_id)
            event: ObjectEvent
            for event in game_map.events.objectEvents.values():
                current_event = event
                if event.scriptPointer:
                    rom.seek(gba.address_from_pointer(event.scriptPointer))
                    script.do_script(version_group, rom, {ScriptCommand.POKEMART: _parse_pokemart})
            i += 1
            progress.update(i)
    progress.finish()

    return shops, shop_items


def write_shops(out: Dict[str, Dict[str, Dict[str, dict]]]):
    print('Writing Shops to Locations')

    def _write_data(child_slugs, area_data, shop_data):
        if len(child_slugs) == 0:
            # Leaf node
            area_data['shops'] = shop_data
        else:
            # Branch node
            leaf_slug = child_slugs.pop(0)
            if 'children' not in area_data:
                area_data['children'] = {}
            if leaf_slug not in area_data['children']:
                area_data['children'][leaf_slug] = {'name': leaf_slug.replace('-', ' ').title()}
            area_data['children'][leaf_slug] = _write_data(child_slugs, area_data['children'][leaf_slug], shop_data)
        return area_data

    for location_slug, vg_data in progressbar.progressbar(out.items()):
        yaml_path = os.path.join(args.out_locations, '{slug}.yaml'.format(slug=location_slug))
        try:
            with open(yaml_path, 'r') as location_yaml:
                data = yaml.load(location_yaml.read())
        except IOError:
            data = {}

        for vg_slug, location_info in vg_data.items():
            # Write shop data, adding area if necessary
            for area_slugs, shop_data in location_info.items():
                area_slugs = area_slugs.split('/')
                root_area = area_slugs.pop(0)
                if root_area not in data[vg_slug]['areas']:
                    data[vg_slug]['areas'][root_area] = {'name': root_area.replace('-', ' ').title()}
                data[vg_slug]['areas'][root_area] = _write_data(area_slugs, data[vg_slug]['areas'][root_area],
                                                                shop_data)

        with open(yaml_path, 'w') as location_yaml:
            yaml.dump(data, location_yaml)


def write_shop_items(used_version_groups, out: List[Dict[str, Any]]):
    print('Writing shop items')

    data = []
    with open(args.out_shop_items, 'r') as shop_items_csv:
        for row in csv.DictReader(shop_items_csv):
            if row['version_group'] not in used_version_groups:
                data.append(row)
    data.extend(out)

    with open(args.out_shop_items, 'w') as shop_items_csv:
        writer = csv.DictWriter(shop_items_csv, data[0].keys())
        writer.writeheader()
        writer.writerows(data)


if __name__ == '__main__':
    # Get config
    argparser = argparse.ArgumentParser(description='Load Gen 3 data.  (R/S uses Rev 1.2 ROMs)')
    argparser.add_argument('--rom', action='append', type=argparse.FileType('rb'), required=True, help='ROM File path')
    argparser.add_argument('--version', action='append', type=str, choices=[version.value for version in Version],
                           help='Version slug to dump')
    argparser.add_argument('--out-pokemon', type=str, required=True, help='Pokemon YAML file dir')
    argparser.add_argument('--out-pokemon_moves', type=str, required=True, help='Pokemon Move CSV file')
    argparser.add_argument('--out-moves', type=str, required=True, help='Move YAML file dir')
    argparser.add_argument('--out-contest_effects', type=str, required=True, help='Contest Effect YAML file dir')
    argparser.add_argument('--out-items', type=str, required=True, help='Item YAML file dir')
    argparser.add_argument('--out-shop_items', type=str, required=True, help='Shop Data CSV file')
    argparser.add_argument('--out-locations', type=str, required=True, help='Location YAML file dir')
    argparser.add_argument('--out-encounters', type=str, required=True, help='Encounter CSV file')
    argparser.add_argument('--write-pokemon', action='store_true', help='Write Pokemon data')
    argparser.add_argument('--write-pokemon_moves', action='store_true', help='Write Pokemon move data')
    argparser.add_argument('--write-moves', action='store_true', help='Write Move data')
    argparser.add_argument('--write-contest_effects', action='store_true', help='Write Contest Effect data')
    argparser.add_argument('--write-items', action='store_true', help='Write Item data')
    argparser.add_argument('--write-shops', action='store_true', help='Write Shop data')
    argparser.add_argument('--write-shop_items', action='store_true', help='Write Shop items')
    argparser.add_argument('--write-encounters', action='store_true', help='Write Shop data')
    global args
    args = argparser.parse_args()

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

    out_moves = {}
    out_contest_effects = {}
    out_items = {}
    out_shops = {}
    out_shop_items = []
    dumped_versions = set()
    dumped_version_groups = set()
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

        vg_moves, vg_contest_effects, vg_move_slugs = get_moves(dump_rom, dump_version_group, dump_version)
        out_moves = group_by_version_group(dump_version_group.value, vg_moves, out_moves)
        out_contest_effects = group_by_version_group(dump_version_group.value, vg_contest_effects, out_contest_effects)
        vg_items, vg_item_slugs = get_items(dump_rom, dump_version_group, dump_version)
        vg_items = update_machines(dump_rom, dump_version, vg_items, vg_move_slugs)
        vg_items = update_berries(dump_rom, dump_version_group, dump_version, vg_items)
        out_items = group_by_version_group(dump_version_group.value, vg_items, out_items)
        vg_shops, vg_shop_items = get_shops(dump_rom, dump_version_group, dump_version, vg_item_slugs, vg_items)
        out_shops = group_by_version_group(dump_version_group.value, vg_shops, out_shops)
        out_shop_items.extend(vg_shop_items)

        dumped_versions.add(dump_version.value)
        dumped_version_groups.add(dump_version_group.value)

    if len(dumped_versions) < len(args.version):
        # Didn't dump all requested versions
        missing_versions = []
        for requested_version in args.version:
            if requested_version not in dumped_versions:
                missing_versions.append(requested_version)
        print('Could not dump these versions because the ROMs were not available: {roms}'.format(
            roms=', '.join(missing_versions)))
        exit(1)
    else:
        if args.write_moves:
            write_moves(out_moves)
        if args.write_contest_effects:
            write_contest_effects(out_contest_effects)
        if args.write_items:
            write_items(out_items)
        if args.write_shops:
            write_shops(out_shops)
        if args.write_shop_items:
            write_shop_items(dumped_version_groups, out_shop_items)
        exit(0)
