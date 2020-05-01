from dataclasses import dataclass
from io import BufferedReader
from pathlib import Path
import struct

import progressbar
from slugify import slugify

from gen3_gc.enums import move_name_changes, type_map, Version
from gen3_gc.strings import ColoStrings, get_string, XdStrings
from inc.yaml import yaml

move_slugs = {}
out = {}


def get_moves(game_path: Path, version: Version, moves_out_path: Path):
    common_rel_path = (game_path.joinpath(Path('common.fsys/common_rel.fdat')))
    assert common_rel_path.is_file()

    common_rel: BufferedReader
    with common_rel_path.open('rb') as common_rel:
        _get_stats(game_path, common_rel, version)
    _pullup_data(version, moves_out_path)

    return out, move_slugs


def _get_stats(game_path: Path, common_rel: BufferedReader, version: Version):
    num_moves = {
        Version.COLOSSEUM: 356,
        Version.XD: 373
    }
    num_moves = num_moves[version]
    stats_offset = {
        Version.COLOSSEUM: 0x11E048,
        Version.XD: 0x0A2748,
    }
    stats_offset = stats_offset[version]
    stats_length = 56
    name_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.COMMON_REL,
        Version.XD: XdStrings.StringTable.COMMON_REL,
    }
    name_table = name_table[version]
    flavor_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.COMMON_REL,
        Version.XD: XdStrings.StringTable.MAIN,
    }
    flavor_table = flavor_table[version]
    target_map = {
        0: 'selected-pokemon',
        1: 'specific-move',
        2: 'all-pokemon',
        3: 'random-opponent',
        4: 'all-opponents',
        5: 'user',
        6: 'all-other-pokemon',
        7: 'opponents-field',
    }
    damage_class_map = {
        1: 'physical',
        2: 'special',
    }

    @dataclass()
    class MoveBaseStats:
        def __init__(self, data: bytes):
            if version == Version.COLOSSEUM:
                data = struct.unpack('>bBBBBB??????4x?x?B3xB3xB6xH10xH2xH4x', data)
                self.priority = data[0]
                self.pp = data[1]
                self.typeId = data[2]
                self.targetId = data[3]
                self.accuracy = data[4]
                self.effectChance = data[5]
                self.contact = data[6]
                self.protect = data[7]
                self.reflectable = data[8]
                self.snatch = data[9]
                self.mirror = data[10]
                self.kingsRock = data[11]
                self.sound = data[12]
                self.isHmMove = data[13]
                self.damageClassId = None
                self.recoil = data[14]
                self.power = data[15]
                self.effectId = data[16] + 1
                self.nameId = data[17]
                self.descriptionId = data[18]
                self.animationId = data[19]
            else:
                data = struct.unpack('>bBBBBB??????4x?x?BB4xB3xB4xH10xH2xH4x', data)
                self.priority = data[0]
                self.pp = data[1]
                self.typeId = data[2]
                self.targetId = data[3]
                self.accuracy = data[4]
                self.effectChance = data[5]
                self.contact = data[6]
                self.protect = data[7]
                self.reflectable = data[8]
                self.snatch = data[9]
                self.mirror = data[10]
                self.kingsRock = data[11]
                self.sound = data[12]
                self.isHmMove = data[13]
                self.damageClassId = data[14]
                self.recoil = data[15]
                self.power = data[16]
                self.effectId = data[17] + 1
                self.nameId = data[18]
                self.descriptionId = data[19]
                self.animationId = data[20]

    def is_shadow(move_id: int):
        # The shadow moves are defined internally as normal type, so this extra check is needed.
        return move_id >= 355

    print('Dumping moves')

    for move_id in range(1, num_moves + 1):
        common_rel.seek(stats_offset + ((move_id - 1) * stats_length))
        move_stats = MoveBaseStats(common_rel.read(stats_length))
        if move_stats.nameId == 0:
            # Dummy move
            continue
        name = get_string(game_path, name_table, move_stats.nameId)
        slug = slugify(name)
        if slug == 'solid-plant' and version == Version.XD:
            # Dummy move
            continue
        move_slugs[move_id] = slug
        flavor = get_string(game_path, flavor_table, move_stats.descriptionId)
        out[slug] = {
            'name': name,
            'power': move_stats.power,
            'type': type_map[move_stats.typeId],
            'accuracy': move_stats.accuracy,
            'pp': move_stats.pp,
            'effect': move_stats.effectId,
            'target': target_map[move_stats.targetId],
            'priority': move_stats.priority,
            'damage_class': None,
            'flavor_text': flavor,
        }
        if version == Version.COLOSSEUM:
            del out[slug]['damage_class']
        else:
            # XD is the first game with move damage classes!  Kind of.  Only for shadow moves.
            if move_stats.damageClassId > 0:
                out[slug]['damage_class'] = damage_class_map[move_stats.damageClassId]
            elif move_stats.typeId < 0x09:
                out[slug]['damage_class'] = 'physical'
            else:
                out[slug]['damage_class'] = 'special'

        if is_shadow(move_id):
            out[slug]['type'] = 'shadow'
        if 0 < move_stats.effectChance < 100:
            out[slug]['effect_chance'] = move_stats.effectChance
        if out[slug]['power'] == 0:
            del out[slug]['power']
        if out[slug]['accuracy'] == 0:
            del out[slug]['accuracy']
        flags = []
        if move_stats.contact:
            flags.append('contact')
        if move_stats.protect:
            flags.append('protect')
        if move_stats.reflectable:
            flags.append('reflectable')
        if move_stats.snatch:
            flags.append('snatch')
        if move_stats.mirror:
            flags.append('mirror')
        if move_stats.kingsRock:
            flags.append('kings-rock')
        if len(flags) > 0:
            out[slug]['flags'] = flags


def _pullup_data(version: Version, moves_out_path: Path):
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
            file_slug = move_name_changes[move_slug]
        elif move_slug == 'solid-plant':
            # Not a name change per se, but this was mis-translated for Colosseum.
            file_slug = 'frenzy-plant'
        else:
            file_slug = move_slug
        yaml_path = moves_out_path.joinpath('{move}.yaml'.format(move=file_slug))

        with yaml_path.open('r') as move_yaml:
            old_move_data = yaml.load(move_yaml.read())
            if version.value not in old_move_data:
                # If the name has changed, try the original name, as it may have been moved already.
                if move_slug in move_name_changes:
                    yaml_path = moves_out_path.joinpath('{move}.yaml'.format(move=move_slug))
                    with yaml_path.open('r') as move_yaml:
                        old_move_data = yaml.load(move_yaml.read())
                else:
                    raise Exception(
                        'Move {move} has no data for version group {version_group}.'.format(
                            move=move_slug,
                            version_group=version.value))
        _pullup_keys = pullup_keys.copy()
        if out[move_slug]['type'] == 'shadow' and version == Version.XD:
            # The shadow move effects have tons of special cases that have been covered already.
            _pullup_keys.append('effect')
        for key in _pullup_keys:
            if key in old_move_data[version.value]:
                out[move_slug][key] = old_move_data[version.value][key]


def write_moves(out_data: dict, moves_out_path: Path):
    print('Writing Moves')
    used_version_groups = set()
    for move_slug, move_data in progressbar.progressbar(out_data.items()):
        yaml_path = moves_out_path.joinpath('{slug}.yaml'.format(slug=move_slug))
        try:
            with yaml_path.open('r') as move_yaml:
                data = yaml.load(move_yaml.read())
        except IOError:
            data = {}
        data.update(move_data)
        used_version_groups.update(move_data.keys())
        with yaml_path.open('w') as move_yaml:
            yaml.dump(data, move_yaml)

    # Remove this version group's data from the new name's file
    for old_name, new_name in move_name_changes.items():
        yaml_path = moves_out_path.joinpath('{slug}.yaml'.format(slug=new_name))
        with yaml_path.open('r') as move_yaml:
            data = yaml.load(move_yaml.read())
        changed = False
        for check_version_group in used_version_groups:
            if check_version_group in data:
                del data[check_version_group]
                changed = True
        if changed:
            with yaml_path.open('w') as move_yaml:
                yaml.dump(data, move_yaml)
