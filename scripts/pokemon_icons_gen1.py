import os
import re
import sys

from inc.yaml import yaml


def icons():
    asm_file = sys.argv[1]
    yaml_dir = sys.argv[2]
    version_group = sys.argv[3]

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
    species_index = 0

    with open(asm_file, mode='rt') as asm_data:
        for asm_line in asm_data:
            entries = asm_line.strip().split()
            for entry in entries:
                entry = entry.rstrip(', ')
                match = re.fullmatch('^SPRITE_(?P<sprite>[\w_]+)$', entry)
                if match:
                    species_slug = _species_order[species_index]
                    sprite = match.group('sprite').lower()
                    yaml_path = os.path.join(yaml_dir, '{species}.yaml'.format(species=species_slug))
                    with open(yaml_path, 'rt') as species_yaml:
                        species_data = yaml.load(species_yaml.read())
                        species_data[version_group]['pokemon'][species_slug]['forms'][species_slug][
                            'icon'] = 'gen1/{sprite}.png'.format(sprite=sprite)
                    with open(yaml_path, 'wt') as species_yaml:
                        yaml.dump(species_data, species_yaml)
                    species_index = species_index + 1


if __name__ == '__main__':
    exit(icons())
