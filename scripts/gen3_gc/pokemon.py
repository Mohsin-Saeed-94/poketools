import csv
from dataclasses import dataclass
from io import BufferedReader
from pathlib import Path
import struct
import sys

import progressbar
from slugify import slugify

from inc.yaml import yaml
from .enums import type_map, Version
from .strings import ColoStrings, get_string, XdStrings

out = {}
species_slugs = {}
pokemon_slugs = {}
out_pokemon_moves = set()

form_default_map = {
    'castform': 'castform-default',
    'deoxys': 'deoxys-normal',
}

slug_overrides = {
    'farfetch-d': 'farfetchd',
}


def get_pokemon(game_path: Path, version: Version, pokemon_out_path: Path, ability_slugs: dict, item_slugs: dict,
                move_slugs: dict, items: dict):
    out.clear()
    species_slugs.clear()
    pokemon_slugs.clear()
    out_pokemon_moves.clear()

    print('Dumping Pokemon')
    species_levelup_data, species_machine_data, species_evolution_data, species_tutor_data = \
        _get_stats(game_path, version, ability_slugs, item_slugs)
    _get_strategy_memo_order()
    _build_forms(version)
    for species_slug, evolution_data in species_evolution_data.items():
        _get_evolution(species_slug, version, evolution_data, item_slugs)
    _handle_specials(version)

    # Move learnsets
    # Get the moves each tm teaches
    machine_moves = {}
    for item_slug, item_data in items.items():
        if 'machine' not in item_data:
            continue
        machine_data = item_data['machine']
        machine_moves[item_slug] = machine_data['move']
    for species_slug, data in species_levelup_data.items():
        _get_levelup_moves(species_slug, version, move_slugs, data)
    for species_slug, data in species_machine_data.items():
        _get_machine_moves(species_slug, version, move_slugs, machine_moves, data)

    for species_slug, data in species_tutor_data.items():
        _get_tutor_moves(species_slug, version, move_slugs, data)

    # _pullup_data(version, pokemon_out_path)

    return out, out_pokemon_moves, species_slugs, pokemon_slugs


def _get_stats(game_path: Path, version: Version, ability_slugs: dict, item_slugs: dict):
    pokemon_offset = {
        Version.COLOSSEUM: 0x12336C,
        Version.XD: 0x029ECC
    }
    pokemon_offset = pokemon_offset[version]
    pokemon_length = {
        Version.COLOSSEUM: 0x011C,
        Version.XD: 0x0124,
    }
    pokemon_length = pokemon_length[version]
    num_species = 412  # This includes some dummy mons in the middle, as in the GBA games
    name_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.COMMON_REL,
        Version.XD: XdStrings.StringTable.COMMON_REL,
    }
    name_table = name_table[version]
    genus_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.PDA_MENU_2,
        Version.XD: XdStrings.StringTable.PDA_MENU_2,
    }
    genus_table = genus_table[version]

    common_rel_path = (game_path.joinpath(Path('common.fsys/common_rel.fdat')))
    assert common_rel_path.is_file()

    growth_rate_map = {
        0x00: 'medium',
        0x01: 'slow-then-very-fast',
        0x02: 'fast-then-very-slow',
        0x03: 'medium-slow',
        0x04: 'fast',
        0x05: 'slow',
    }

    egg_group_map = {
        0x01: 'monster',
        0x02: 'water1',
        0x03: 'bug',
        0x04: 'flying',
        0x05: 'ground',
        0x06: 'fairy',
        0x07: 'plant',
        0x08: 'humanshape',
        0x09: 'water3',
        0x0A: 'mineral',
        0x0B: 'indeterminate',
        0x0C: 'water2',
        0x0D: 'ditto',
        0x0E: 'dragon',
        0x0F: 'no-eggs',
    }

    @dataclass()
    class PokemonStats:
        def __init__(self, data: bytes):
            self.growthRate = growth_rate_map[data[0]]
            self.captureRate = None
            self.femaleRate = None
            self.experience = None
            self.happiness = None
            self.height = None
            self.weight = None
            self.cryId = None
            self.sortPosition = None
            self.dexNationalNum = None
            self.dexHoennNum = None
            self.nameId = None
            self.genusId = None
            self.modelId = None
            self.types = None
            self.abilities = None
            self.machines = None
            self.tutors = None
            self.hp = None
            self.attack = None
            self.defense = None
            self.specialAttack = None
            self.specialDefense = None
            self.speed = None
            self.evHp = None
            self.evAttack = None
            self.evDefense = None
            self.evSpecialAttack = None
            self.evSpecialDefense = None
            self.evSpeed = None
            self.evolution = None
            self.levelupMoves = None
            self.iconId = None
            if version == Version.COLOSSEUM:
                data = struct.unpack('>BBBxIHHHHHHHHII14xHBBBB58sBBHH16xHHHHHHHHHHHH30s80s4xH12x', data)
                self.growthRate = growth_rate_map[data[0]]
                self.captureRate = data[1]
                self.femaleRate = data[2]
                self.experience = data[3]
                self.happiness = data[4]
                self.height = data[5]
                self.weight = data[6]
                self.cryId = data[7]
                self.sortPosition = data[8]
                self.dexNationalNum = data[9]
                self.dexHoennNum = data[10]
                # self.hatchSteps = data[11]
                self.nameId = data[12]
                self.genusId = data[13]
                self.modelId = data[14]
                self.types = []
                for type_id in [data[15], data[16]]:
                    if type_map[type_id] not in self.types:
                        self.types.append(type_map[type_id])
                self.abilities = []
                for ability_id in [data[17], data[18]]:
                    if ability_id > 0:
                        self.abilities.append(ability_slugs[ability_id])
                self.machines = data[19]
                # self.eggGroups = []
                # for egg_group_id in [data[20], data[21]]:
                #     if egg_group_id > 0 and egg_group_map[egg_group_id] not in self.eggGroups:
                #         self.eggGroups.append(egg_group_map[egg_group_id])
                # if data[22] > 0:
                #     self.item1 = item_slugs[data[22]]
                # else:
                #     self.item1 = None
                # if data[23] > 0:
                #     self.item2 = item_slugs[data[23]]
                # else:
                #     self.item2 = None
                self.hp = data[24]
                self.attack = data[25]
                self.defense = data[26]
                self.specialAttack = data[27]
                self.specialDefense = data[28]
                self.speed = data[29]
                self.evHp = data[30]
                self.evAttack = data[31]
                self.evDefense = data[32]
                self.evSpecialAttack = data[33]
                self.evSpecialDefense = data[34]
                self.evSpeed = data[35]
                self.evolution = data[36]
                self.levelupMoves = data[37]
                self.iconId = data[38]
            else:
                data = struct.unpack('>BBBxHHHHHH2xHH2xII14xHBBBB58s12s20xHHHHHHHHHHHH30s80s16x', data)
                self.growthRate = growth_rate_map[data[0]]
                self.captureRate = data[1]
                self.femaleRate = data[2]
                self.experience = data[3]
                self.happiness = data[4]
                self.height = data[5]
                self.weight = data[6]
                self.cryId = data[7]
                self.sortPosition = data[8]
                self.dexNationalNum = data[9]
                self.dexHoennNum = data[10]
                self.nameId = data[11]
                self.genusId = data[12]
                self.modelId = data[13]
                self.types = []
                for type_id in [data[14], data[15]]:
                    if type_map[type_id] not in self.types:
                        self.types.append(type_map[type_id])
                self.abilities = []
                for ability_id in [data[16], data[17]]:
                    if ability_id > 0:
                        self.abilities.append(ability_slugs[ability_id])
                self.machines = data[18]
                self.tutors = data[19]
                self.hp = data[20]
                self.attack = data[21]
                self.defense = data[22]
                self.specialAttack = data[23]
                self.specialDefense = data[24]
                self.speed = data[25]
                self.evHp = data[26]
                self.evAttack = data[27]
                self.evDefense = data[28]
                self.evSpecialAttack = data[29]
                self.evSpecialDefense = data[30]
                self.evSpeed = data[31]
                self.evolution = data[32]
                self.levelupMoves = data[33]

    levelup_data = {}
    machine_data = {}
    evolution_data = {}
    tutor_data = {}

    common_rel_file: BufferedReader
    with common_rel_path.open('rb') as common_rel_file:
        for species_id in range(1, num_species + 1):
            common_rel_file.seek(pokemon_offset + ((species_id - 1) * pokemon_length))
            stats = PokemonStats(common_rel_file.read(pokemon_length))
            if stats.nameId == 0x00:
                # Dummy mon
                continue
            name = get_string(game_path, name_table, stats.nameId)
            species_slug = slugify(name.replace('♀', '-f').replace('♂', '-m'))
            if species_slug in slug_overrides:
                species_slug = slug_overrides[species_slug]
            species_slugs[species_id] = species_slug
            if species_slug in form_default_map:
                pokemon_slug = form_default_map[species_slug]
            else:
                pokemon_slug = species_slug
            pokemon_slugs[species_slug] = [pokemon_slug]
            out[species_slug] = {
                'name': name,
                'position': stats.sortPosition,
                'numbers': {
                    'national': stats.dexNationalNum
                },
            }

            pokemon = {
                'name': name,
                'default': True,
                'forms_switchable': False,
                'forms_note': None,
                'capture_rate': stats.captureRate,
                'experience': stats.experience,
                'types': stats.types,
                'stats': {
                    'hp': {
                        'base_value': stats.hp,
                        'effort_change': stats.evHp,
                    },
                    'attack': {
                        'base_value': stats.attack,
                        'effort_change': stats.evAttack,
                    },
                    'defense': {
                        'base_value': stats.defense,
                        'effort_change': stats.evDefense,
                    },
                    'speed': {
                        'base_value': stats.speed,
                        'effort_change': stats.evSpeed,
                    },
                    'special-attack': {
                        'base_value': stats.specialAttack,
                        'effort_change': stats.evSpecialDefense,
                    },
                    'special-defense': {
                        'base_value': stats.hp,
                        'effort_change': stats.evSpecialDefense,
                    },
                },
                'growth_rate': stats.growthRate,
                'female_rate': stats.femaleRate,
                # 'hatch_steps': stats.hatchSteps,
                # 'egg_groups': stats.eggGroups,
                # 'wild_held_items': {},
                'happiness': stats.happiness,
                'abilities': {},
                'genus': '{genus} Pokémon'.format(genus=get_string(game_path, genus_table, stats.genusId)),
                'height': None,
                'weight': None,
            }

            # Gender
            if pokemon['female_rate'] == 255:
                # Genderless
                del pokemon['female_rate']
            else:
                pokemon['female_rate'] = round((pokemon['female_rate']) / 254 * 100)

            # Wild held items
            # if stats.item1 or stats.item2:
            #     if stats.item1 == stats.item2:
            #         pokemon['wild_held_items'][stats.item1] = 100
            #     else:
            #         # These chances are hard coded in
            #         if stats.item1:
            #             pokemon['wild_held_items'][stats.item1] = 50
            #         if stats.item2:
            #             pokemon['wild_held_items'][stats.item2] = 5
            # else:
            #     del pokemon['wild_held_items']

            # Abilities
            i = 1
            for ability in stats.abilities:
                if not ability:
                    continue
                pokemon['abilities'][ability] = {'hidden': False, 'position': i}
                i += 1

            if version == Version.COLOSSEUM:
                # In Colosseum, Height and Weight have reverted to being stored in the same
                # ridiculous way as Gen 2.
                height_parts = str(stats.height / 100).split('.')
                height_ft = int(height_parts[0])
                height_in = int(height_parts[1])
                pokemon['height'] = max(1, round(((12 * height_ft) + height_in) / 3.937))
                weight_lbs = stats.weight / 10
                pokemon['weight'] = max(1, round(weight_lbs * 4.536))
            else:
                # Hooray for sanity
                pokemon['height'] = stats.height
                pokemon['weight'] = stats.weight

            # Colosseum and XD have no in-game Pokedex descriptions, so no flavor text.

            out[species_slug]['pokemon'] = {pokemon_slug: pokemon}

            # This will be processed later
            levelup_data[species_slug] = stats.levelupMoves
            machine_data[species_slug] = stats.machines
            evolution_data[species_slug] = stats.evolution
            if stats.tutors:
                tutor_data[species_slug] = stats.tutors

    return levelup_data, machine_data, evolution_data, tutor_data


def _get_strategy_memo_order():
    slugs = list(species_slugs.values())
    slugs.sort()

    i = 1
    for slug in slugs:
        out[slug]['numbers']['orre-strategy-memo'] = i
        i += 1


def _get_levelup_moves(species_slug: str, version: Version, move_slugs: dict, levelup_data: bytes):
    @dataclass()
    class LevelupEntry:
        def __init__(self, data: bytes):
            data = struct.unpack('>BxH', data)
            self.level = data[0]
            if data[1] > 0:
                self.move = move_slugs[data[1]]
            else:
                self.move = None

    cursor = 0
    for index in range(0, 20):
        levelup_entry = LevelupEntry(levelup_data[cursor:cursor + 4])
        if levelup_entry.move is None:
            continue
        cursor += 4
        for pokemon_slug in pokemon_slugs[species_slug]:
            out_pokemon_moves.add(tuple({
                                            'species': species_slug,
                                            'pokemon': pokemon_slug,
                                            'version_group': version.value,
                                            'move': levelup_entry.move,
                                            'learn_method': 'level-up',
                                            'level': levelup_entry.level,
                                            'machine': None,
                                        }.items()))


def _get_machine_moves(species_slug: str, version: Version, move_slugs: dict, machine_moves: dict, machine_data: bytes):
    # Ignore HMs, as they are not in these games
    for machine_id in range(1, 51):
        can_learn = bool(machine_data[machine_id - 1])
        if can_learn:
            machine_type = 'TM'
            machine_number = machine_id
            item_slug = '{type}{number:02}'.format(type=machine_type.lower(), number=machine_number)

            for pokemon_slug in pokemon_slugs[species_slug]:
                out_pokemon_moves.add(tuple({
                                                'species': species_slug,
                                                'pokemon': pokemon_slug,
                                                'version_group': version.value,
                                                'move': machine_moves[item_slug],
                                                'learn_method': 'machine',
                                                'level': None,
                                                'machine': item_slug,
                                            }.items()))


def _get_tutor_moves(species_slug: str, version: Version, move_slugs: dict, tutor_data: bytes):
    tutor_move_map = {
        0: 'body-slam',
        1: 'double-edge',
        2: 'seismic-toss',
        3: 'mimic',
        4: 'nightmare',
        5: 'thunder-wave',
        6: 'swagger',
        7: 'icy-wind',
        8: 'substitute',
        9: 'sky-attack',
        10: 'selfdestruct',
        11: 'dream-eater',
    }
    for tutor_id, move_slug in tutor_move_map.items():
        can_learn = bool(tutor_data[tutor_id])
        if can_learn:
            for pokemon_slug in pokemon_slugs[species_slug]:
                out_pokemon_moves.add(tuple({
                                                'species': species_slug,
                                                'pokemon': pokemon_slug,
                                                'version_group': version.value,
                                                'move': move_slug,
                                                'learn_method': 'tutor',
                                                'level': None,
                                                'machine': None,
                                            }.items()))


def _build_forms(version: Version):
    for species_slug in species_slugs.values():
        if species_slug in form_default_map:
            form_slug = form_default_map[species_slug]
        else:
            form_slug = '{slug}-default'.format(slug=pokemon_slugs[species_slug][0])
        form_name = out[species_slug]['name']

        form = {
            'name': form_name,
            'form_name': 'Default Form',
            'default': True,
            'battle_only': False,
            'sprites': [
                '{version_group}/{slug}.png'.format(version_group=version.value, slug=form_slug),
                '{version_group}/shiny/{slug}.png'.format(version_group=version.value, slug=form_slug),
            ],
            'art': ['{slug}.png'.format(slug=form_slug)],
            'cry': 'gen5/{slug}.webm'.format(slug=form_slug),
        }
        out[species_slug]['pokemon'][pokemon_slugs[species_slug][0]]['forms'] = {
            pokemon_slugs[species_slug][0]: form
        }


def _get_evolution(species_slug: str, version: Version, evolution_data: bytes, item_slugs: dict):
    @dataclass()
    class Evolution:
        def __init__(self, data: bytes):
            data = struct.unpack('>BxHH', data)
            self.methodTrigger = data[0]
            self.param = data[1]
            self.evolvesIntoId = data[2]

    # Each species has 5 entries in the evolution table.  Most of them are blank, but this leaves enough room
    # to store all of Eevee's evolutions.
    num_entries = 5
    evolution_length = 6
    cursor = 0
    for i in range(0, num_entries):
        evolution = Evolution(evolution_data[cursor:cursor + evolution_length])
        cursor += evolution_length
        if evolution.methodTrigger == 0:
            continue

        evolves_into = species_slugs[evolution.evolvesIntoId]
        out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
            'evolution_parent': '{species}/{pokemon}'.format(species=species_slug,
                                                             pokemon=pokemon_slugs[species_slug][0])
        })

        # The methodTrigger entry doesn't map cleanly to our data format, so everything
        # is a special case.
        if evolution.methodTrigger == 0x01:
            # Friendship, any time
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_happiness': 220,
                    }
                }
            })
        elif evolution.methodTrigger == 0x02:
            # Friendship, during the day
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_happiness': 220,
                        'time_of_day': ['day'],
                    }
                }
            })
        elif evolution.methodTrigger == 0x03:
            # Friendship, during the night
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_happiness': 220,
                        'time_of_day': ['night'],
                    }
                }
            })
        elif evolution.methodTrigger == 0x04:
            # Minimum level
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_level': evolution.param,
                    }
                }
            })
        elif evolution.methodTrigger == 0x05:
            # Traded
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'trade': {}
                }
            })
        elif evolution.methodTrigger == 0x06:
            # Traded, with item
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'trade': {
                        'held_item': item_slugs[evolution.param],
                    }
                }
            })
        elif evolution.methodTrigger == 0x07:
            # Use item
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'use-item': {
                        'trigger_item': item_slugs[evolution.param],
                    }
                }
            })
        elif evolution.methodTrigger == 0x08:
            # Minimum level, Attack > Defense
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_level': evolution.param,
                        'physical_stats_difference': 1,
                    }
                }
            })
        elif evolution.methodTrigger == 0x09:
            # Minimum level, Attack == Defense
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_level': evolution.param,
                        'physical_stats_difference': 0,
                    }
                }
            })
        elif evolution.methodTrigger == 0x0A:
            # Minimum level, Attack < Defense
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_level': evolution.param,
                        'physical_stats_difference': -1,
                    }
                }
            })
        elif evolution.methodTrigger == 0x0B or evolution.methodTrigger == 0x0C:
            # Minimum level (Wurmple to Silcoon or Cascoon)
            # Because our data structure is flipped from the way the games store it,
            # we already know what species the Wurmple will evolve into.  As such,
            # this is stored no differently from a normal level_up evolution condition.
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_level': evolution.param,
                    }
                }
            })
        elif evolution.methodTrigger == 0x0D:
            # Minimum level
            # This is the Ninjask side of the evolution and functions no differently
            # from a normal level_up evolution condition
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_level': evolution.param,
                    }
                }
            })
        elif evolution.methodTrigger == 0x0E:
            # Shedinja
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'shed': {}
                }
            })
        elif evolution.methodTrigger == 0x0F:
            # Minimum beauty
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_beauty': evolution.param,
                    }
                }
            })
        elif evolution.methodTrigger == 0x10:
            # Player has item in their bag
            out[evolves_into]['pokemon'][pokemon_slugs[evolves_into][0]].update({
                'evolution_conditions': {
                    'level-up': {
                        'minimum_happiness': 220,
                        'bag_item': item_slugs[evolution.param]
                    }
                }
            })
        else:
            raise Exception('0x{method_trigger:2x} is not a valid method/trigger value'.format(
                method_trigger=evolution.methodTrigger))


def _handle_specials(version: Version):
    # Generate the Unown forms
    # A-Z
    unown_letters = [char.to_bytes(1, byteorder=sys.byteorder).decode('ascii') for char in range(0x41, 0x5B)]
    # Add the new ! and ? forms
    unown_letters.extend(['!', '?'])
    for letter in unown_letters:
        form_slug = 'unown-{letter}'.format(
            letter=letter.lower().replace('!', 'exclamation').replace('?', 'question'))
        form = out['unown']['pokemon']['unown']['forms']['unown'].copy()
        form.update({
            'name': 'UNOWN ({letter})'.format(letter=letter.upper()),
            'form_name': letter.upper(),
            'default': letter.upper() == 'A',
            'battle_only': False,
            'sprites': [
                '{version_group}/{slug}.png'.format(version_group=version.value, slug=form_slug),
                '{version_group}/shiny/{slug}.png'.format(version_group=version.value, slug=form_slug),
            ],
            'art': ['unown-f.png'],
        })
        out['unown']['pokemon']['unown']['forms'][form_slug] = form
    del out['unown']['pokemon']['unown']['forms']['unown']

    # Castform's transformations are special cases in the battle code
    castform_type_map = {
        'sunny': 'fire',
        'rainy': 'water',
        'snowy': 'ice',
    }
    for form_slug, form_type in castform_type_map.items():
        pokemon_slug = 'castform-{form}'.format(form=form_slug)
        pokemon_name = '{form} CASTFORM'.format(form=form_slug.title())
        pokemon_slugs['castform'].append(pokemon_slug)
        pokemon = out['castform']['pokemon'][pokemon_slugs['castform'][0]].copy()
        pokemon.update({
            'name': pokemon_name,
            'default': False,
            'types': [form_type],
        })
        form = pokemon['forms'][pokemon_slugs['castform'][0]].copy()
        form.update({
            'name': pokemon_name,
            'form_name': '{form} Form'.format(form=form_slug.title()),
            'default': True,
            'battle_only': True,
            'sprites': [
                '{version_group}/{slug}'.format(version_group=version.value, slug=pokemon_slug),
                '{version_group}/shiny/{slug}'.format(version_group=version.value, slug=pokemon_slug),
            ],
        })
        pokemon['forms'] = {pokemon_slug: form}
        out['castform']['pokemon'][pokemon_slug] = pokemon

        # Cleanup Deoxys - it only has the Normal Form in these games.
        # Update the names for R/S
        out['deoxys']['pokemon'][pokemon_slugs['deoxys'][0]]['name'] = 'Normal DEOXYS'
        out['deoxys']['pokemon'][pokemon_slugs['deoxys'][0]]['forms'][pokemon_slugs['deoxys'][0]].update({
            'name': 'Normal DEOXYS',
            'form_name': 'Normal Forme',
        })


def _pullup_data(version: Version, pokemon_out_path: Path):
    pullup_keys = [
        'forms_note',
    ]
    print('Using existing data')
    for species_slug in progressbar.progressbar(species_slugs.values()):
        yaml_path = pokemon_out_path.joinpath('{species}.yaml'.format(species=species_slug))
        with yaml_path.open('r') as species_yaml:
            old_species_data = yaml.load(species_yaml.read())
            for key in pullup_keys:
                for pokemon_slug, pokemon_data in old_species_data[version.value]['pokemon'].items():
                    if pokemon_slug not in out[species_slug]['pokemon']:
                        # Not all versions have the same Pokemon as their version group partner.
                        continue
                    if key in pokemon_data:
                        out[species_slug]['pokemon'][pokemon_slug][key] = pokemon_data[key]
                    elif key in out[species_slug]['pokemon'][pokemon_slug]:
                        del out[species_slug]['pokemon'][pokemon_slug][key]


def write_pokemon(out_data: dict, pokemon_out_path: Path):
    print('Writing Pokemon')
    for species_slug, species_data in progressbar.progressbar(out_data.items()):
        yaml_path = pokemon_out_path.joinpath('{slug}.yaml'.format(slug=species_slug))
        try:
            with yaml_path.open('r') as species_yaml:
                data = yaml.load(species_yaml.read())
        except IOError:
            data = {}
        data.update(species_data)
        with yaml_path.open('w') as species_yaml:
            yaml.dump(data, species_yaml)


def write_pokemon_moves(used_version_groups, out_data: set, pokemon_move_out_path: Path):
    print('Writing Pokemon moves')

    # Get existing data, removing those that have just been ripped.
    delete_learn_methods = [
        'level-up',
        'machine',
        'tutor',
    ]
    data = []
    with pokemon_move_out_path.open('r') as pokemon_moves_csv:
        for row in progressbar.progressbar(csv.DictReader(pokemon_moves_csv)):
            if row['version_group'] not in used_version_groups or row['learn_method'] not in delete_learn_methods:
                data.append(row)

    data.extend([dict(row) for row in out_data])
    with pokemon_move_out_path.open('w') as pokemon_moves_csv:
        writer = csv.DictWriter(pokemon_moves_csv, fieldnames=data[0].keys())
        writer.writeheader()
        for row in progressbar.progressbar(data):
            writer.writerow(row)
