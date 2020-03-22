# Rip Gen 2 pokemon data
# Usage:
# pokemon_gen2.py pokemon <path to gen 2 rom> <path to existing Pokemon YAML files>

import os
import sys
from inc import pokemon_text
from inc.yaml import remove_anchors, yaml


def getFile() -> bytes:
    return open(sys.argv[1], 'rb').read()


def pokemon():
    # A fixed species order is fine, as this is a snapshot in time
    _species_order = [
        'bulbasaur',
        'ivysaur',
        'venusaur',
        'charmander',
        'charmeleon',
        'charizard',
        'squirtle',
        'wartortle',
        'blastoise',
        'caterpie',
        'metapod',
        'butterfree',
        'weedle',
        'kakuna',
        'beedrill',
        'pidgey',
        'pidgeotto',
        'pidgeot',
        'rattata',
        'raticate',
        'spearow',
        'fearow',
        'ekans',
        'arbok',
        'pikachu',
        'raichu',
        'sandshrew',
        'sandslash',
        'nidoran-f',
        'nidorina',
        'nidoqueen',
        'nidoran-m',
        'nidorino',
        'nidoking',
        'clefairy',
        'clefable',
        'vulpix',
        'ninetales',
        'jigglypuff',
        'wigglytuff',
        'zubat',
        'golbat',
        'oddish',
        'gloom',
        'vileplume',
        'paras',
        'parasect',
        'venonat',
        'venomoth',
        'diglett',
        'dugtrio',
        'meowth',
        'persian',
        'psyduck',
        'golduck',
        'mankey',
        'primeape',
        'growlithe',
        'arcanine',
        'poliwag',
        'poliwhirl',
        'poliwrath',
        'abra',
        'kadabra',
        'alakazam',
        'machop',
        'machoke',
        'machamp',
        'bellsprout',
        'weepinbell',
        'victreebel',
        'tentacool',
        'tentacruel',
        'geodude',
        'graveler',
        'golem',
        'ponyta',
        'rapidash',
        'slowpoke',
        'slowbro',
        'magnemite',
        'magneton',
        'farfetchd',
        'doduo',
        'dodrio',
        'seel',
        'dewgong',
        'grimer',
        'muk',
        'shellder',
        'cloyster',
        'gastly',
        'haunter',
        'gengar',
        'onix',
        'drowzee',
        'hypno',
        'krabby',
        'kingler',
        'voltorb',
        'electrode',
        'exeggcute',
        'exeggutor',
        'cubone',
        'marowak',
        'hitmonlee',
        'hitmonchan',
        'lickitung',
        'koffing',
        'weezing',
        'rhyhorn',
        'rhydon',
        'chansey',
        'tangela',
        'kangaskhan',
        'horsea',
        'seadra',
        'goldeen',
        'seaking',
        'staryu',
        'starmie',
        'mr-mime',
        'scyther',
        'jynx',
        'electabuzz',
        'magmar',
        'pinsir',
        'tauros',
        'magikarp',
        'gyarados',
        'lapras',
        'ditto',
        'eevee',
        'vaporeon',
        'jolteon',
        'flareon',
        'porygon',
        'omanyte',
        'omastar',
        'kabuto',
        'kabutops',
        'aerodactyl',
        'snorlax',
        'articuno',
        'zapdos',
        'moltres',
        'dratini',
        'dragonair',
        'dragonite',
        'mewtwo',
        'mew',
        'chikorita',
        'bayleef',
        'meganium',
        'cyndaquil',
        'quilava',
        'typhlosion',
        'totodile',
        'croconaw',
        'feraligatr',
        'sentret',
        'furret',
        'hoothoot',
        'noctowl',
        'ledyba',
        'ledian',
        'spinarak',
        'ariados',
        'crobat',
        'chinchou',
        'lanturn',
        'pichu',
        'cleffa',
        'igglybuff',
        'togepi',
        'togetic',
        'natu',
        'xatu',
        'mareep',
        'flaaffy',
        'ampharos',
        'bellossom',
        'marill',
        'azumarill',
        'sudowoodo',
        'politoed',
        'hoppip',
        'skiploom',
        'jumpluff',
        'aipom',
        'sunkern',
        'sunflora',
        'yanma',
        'wooper',
        'quagsire',
        'espeon',
        'umbreon',
        'murkrow',
        'slowking',
        'misdreavus',
        'unown',
        'wobbuffet',
        'girafarig',
        'pineco',
        'forretress',
        'dunsparce',
        'gligar',
        'steelix',
        'snubbull',
        'granbull',
        'qwilfish',
        'scizor',
        'shuckle',
        'heracross',
        'sneasel',
        'teddiursa',
        'ursaring',
        'slugma',
        'magcargo',
        'swinub',
        'piloswine',
        'corsola',
        'remoraid',
        'octillery',
        'delibird',
        'mantine',
        'skarmory',
        'houndour',
        'houndoom',
        'kingdra',
        'phanpy',
        'donphan',
        'porygon2',
        'stantler',
        'smeargle',
        'tyrogue',
        'hitmontop',
        'smoochum',
        'elekid',
        'magby',
        'miltank',
        'blissey',
        'raikou',
        'entei',
        'suicune',
        'larvitar',
        'pupitar',
        'tyranitar',
        'lugia',
        'ho-oh',
        'celebi',
    ]

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
        0x0A: 'type_10',
        0x0B: 'type_11',
        0x0C: 'type_12',
        0x0D: 'type_13',
        0x0E: 'type_14',
        0x0F: 'type_15',
        0x10: 'type_16',
        0x11: 'type_17',
        0x12: 'type_18',
        0x13: 'curse_t',
        0x14: 'fire',
        0x15: 'water',
        0x16: 'grass',
        0x17: 'electric',
        0x18: 'psychic',
        0x19: 'ice',
        0x1A: 'dragon',
        0x1B: 'dark',
    }

    data = getFile()
    yaml_dir = sys.argv[2]

    # What version is this?
    versiongroupmap = {
        'POKEMON_GLDAAUE': 'gold-silver',
        'POKEMON_SLVAAXE': 'gold-silver',
        'PM_CRYSTAL\x00BYTE': 'crystal'
    }
    version_group = data[0x134:0x143]
    version_group = versiongroupmap[version_group.decode('ascii')]

    # Iterate over the pokemon base stats table
    if version_group == 'gold-silver':
        stats_offset = 0x51B0B
    else:
        stats_offset = 0x51424
    stats_length = 32
    species_index = 0
    for species_slug in _species_order:
        # Get Pokemon data entry
        start = stats_offset + (species_index * stats_length)
        end = start + stats_length
        entry = data[start:end]

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
        capture_rate = entry[9]
        experience = entry[10]
        type_1 = _type_map[entry[7]]
        type_2 = _type_map[entry[8]]
        if type_1 == type_2:
            types = [type_1]
        else:
            types = [type_1, type_2]

        # Write data
        yaml_path = os.path.join(yaml_dir, '{species}.yaml'.format(species=species_slug))
        with open(yaml_path, 'rt') as species_yaml:
            species_data = yaml.load(species_yaml.read())
            remove_anchors(species_data)
            species_data[version_group]['pokemon'][species_slug].update({
                'capture_rate': capture_rate,
                'experience': experience,
                'types': types,
                'stats': stats,
            })
        with open(yaml_path, 'wt') as species_yaml:
            yaml.dump(species_data, species_yaml)
        print('Updated {count:3}/{total:3} ({pokemon})'.format(count=species_index + 1, total=len(_species_order),
                                                               pokemon=species_slug))

        species_index += 1


if __name__ == '__main__':
    pokemon_text.register()

    exit(pokemon())
