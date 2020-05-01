from dataclasses import dataclass
from io import BufferedReader
from pathlib import Path
import struct

from slugify import slugify

from gen3_gc.enums import type_map, Version
from gen3_gc.strings import ColoStrings, get_string, XdStrings


def get_moves(game_path: Path, version: Version):
    move_slugs = {}
    out = {}

    common_rel_path = (game_path / 'common.fsys' / 'common_rel.fdat')
    assert common_rel_path.is_file()

    common_rel: BufferedReader
    with common_rel_path.open('rb') as common_rel:
        out, move_slugs = _get_stats(game_path, common_rel, version)


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

    def is_shadow(move_id: int):
        return move_id >= 355

    out = {}
    move_slugs = {}

    @dataclass()
    class MoveBaseStats:
        def __init__(self, data: bytes):
            if version == Version.COLOSSEUM:
                data = struct.unpack('>bBBBBB??????4x?x?B3xB3xB6xH10xH2xH4x', data)
                self.priority = data[0]
                self.pp = data[1]
                self.type = type_map[data[2]]
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
                self.physicalSpecial = None
                self.recoil = data[14]
                self.power = data[15]
                self.effectId = data[16] + 1
                self.nameId = data[17]
                self.descriptionId = data[18]
                self.animationId = data[19]
            else:
                data = struct.unpack('>bBBBBB??????4x?x??B4xB3xB4xH10xH2xH4x', data)
                self.priority = data[0]
                self.pp = data[1]
                self.type = type_map[data[2]]
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
                self.physicalSpecial = data[14]
                self.recoil = data[15]
                self.power = data[16]
                self.effectId = data[17] + 1
                self.nameId = data[18]
                self.descriptionId = data[19]
                self.animationId = data[20]

    for move_id in range(1, num_moves + 1):
        common_rel.seek(stats_offset + ((move_id - 1) * stats_length))
        move_stats = MoveBaseStats(common_rel.read(stats_length))
        if move_stats.nameId == 0:
            # Dummy move
            continue
        name = get_string(game_path, name_table, move_stats.nameId)
        slug = slugify(name)
        move_slugs[move_id] = slug
        flavor = get_string(game_path, flavor_table, move_stats.descriptionId)
        out[slug] = {
            'name': name,
            'power': move_stats.power,
            'type': move_stats.type,
            'accuracy': move_stats.accuracy,
            'pp': move_stats.pp,
            'effect': move_stats.effectId,
            'target': target_map[move_stats.targetId],
            'priority': move_stats.priority,
            'flavor_text': flavor,
        }

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

    return out, move_slugs
