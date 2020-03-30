import os
import sys

from inc.yaml import yaml

yaml_dir = sys.argv[1]

version_group_xy = 'x-y'
version_group_oras = 'omega-ruby-alpha-sapphire'
ignored = []
with os.scandir(yaml_dir) as it:
    file: os.DirEntry
    for file in it:
        # Skip not yaml files
        if not file.is_file() and not file.name.endswith('.yaml') and not file.name.endswith('.yml'):
            continue

        print('Loading {file}... '.format(file=file.name), end='')
        with open(file, 'rt') as species_yaml:
            species_data = yaml.load(species_yaml.read())

        if version_group_oras not in species_data:
            # Ignore new Pokemon
            ignored.append(file.name)
            print('Ignored')
            continue

        for pokemon, pokemon_data in species_data[version_group_oras]['pokemon'].items():
            # Make sure there's data to pull up from
            if pokemon not in species_data[version_group_xy]['pokemon']:
                continue
            for form, form_data in pokemon_data['forms'].items():
                # Get X/Y sprites
                if form not in species_data[version_group_xy]['pokemon'][pokemon]['forms'] or \
                        'sprites' not in species_data[version_group_xy]['pokemon'][pokemon]['forms'][form]:
                    continue
                xy_sprites = species_data[version_group_xy]['pokemon'][pokemon]['forms'][form]['sprites']
                oras_sprites = []
                sprite: str
                for sprite in xy_sprites:
                    oras_sprites.append(sprite.replace(version_group_xy, version_group_oras))

                species_data[version_group_oras]['pokemon'][pokemon]['forms'][form]['sprites'] = oras_sprites

        with open(file, 'wt') as species_yaml:
            yaml.dump(species_data, species_yaml)
        print('Done!')

print("Ignored {count} files:\n{pokemon}".format(count=len(ignored), pokemon="\n".join(ignored)))
