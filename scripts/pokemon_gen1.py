# Rip Gen 1 pokemon data
# Usage:
# pokemon_gen1.py pokemon <path to gen 1 rom> <path to existing Pokemon YAML files>

import os
import sys

from ruamel.yaml import YAML

import pokemon_text

yaml = YAML()
yaml.default_flow_style = False
yaml.indent(mapping=2, sequence=4, offset=2)


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
    ]

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

    data = getFile()
    yaml_dir = sys.argv[2]

    # What version is this?
    versiongroupmap = {
        'POKEMON RED': 'red-blue',
        'POKEMON BLUE': 'red-blue',
        'POKEMON YELLOW': 'yellow'
    }
    version_group = data[0x134:0x143].rstrip(b'\x00')
    version_group = versiongroupmap[version_group.decode('ascii')]

    # Iterate over the pokemon base stats table
    stats_offset = 0x383DE
    stats_length = 28
    species_index = 0
    for species_slug in _species_order:
        # Get Pokemon data entry
        # Mew is stored in a different place in red/blue
        if species_slug == 'mew' and version_group == 'red-blue':
            start = 0x425B
        else:
            start = stats_offset + (species_index * stats_length)
        end = start + stats_length
        entry = data[start:end]

        # Base stats
        stats = {
            'hp': entry[1],
            'attack': entry[2],
            'defense': entry[3],
            'speed': entry[4],
            'special': entry[5]
        }
        capture_rate = entry[8]
        experience = entry[9]
        type_1 = _type_map[entry[6]]
        type_2 = _type_map[entry[7]]
        if type_1 == type_2:
            types = [type_1]
        else:
            types = [type_1, type_2]

        # Write data
        yaml_path = os.path.join(yaml_dir, '{species}.yaml'.format(species=species_slug))
        with open(yaml_path, 'rt') as species_yaml:
            species_data = yaml.load(species_yaml.read())
            species_data[version_group]['pokemon'][species_slug].update({
                'capture_rate': capture_rate,
                'experience': experience,
                'types': types
            })
            for stat, value in stats.items():
                species_data[version_group]['pokemon'][species_slug]['stats'].update({
                    # Gen 1/2 use a different EV system than modern games.
                    stat: {
                        'base_value': value,
                        'effort_change': value
                    }
                })
            # Remove obsolete data
            try:
                del species_data[version_group]['pokemon'][species_slug]['stats']['special-attack']
            except KeyError:
                # Already deleted
                pass
            try:
                del species_data[version_group]['pokemon'][species_slug]['stats']['special-defense']
            except KeyError:
                # Already deleted
                pass
        with open(yaml_path, 'wt') as species_yaml:
            yaml.dump(species_data, species_yaml)
        print('Updated {count:3}/{total:3} ({pokemon})'.format(count=species_index + 1, total=len(_species_order),
                                                               pokemon=species_slug))

        species_index += 1


if __name__ == '__main__':
    pokemon_text.register()

    exit(pokemon())
