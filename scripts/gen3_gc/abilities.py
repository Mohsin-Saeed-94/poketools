from pathlib import Path

import progressbar
from slugify import slugify

from inc.yaml import yaml
from .enums import ability_name_changes, Version
from .strings import ColoStrings, get_string, XdStrings

out = {}
ability_slugs = {}


def get_abilities(game_path: Path, version: Version, abilities_out_path: Path):
    out.clear()
    ability_slugs.clear()

    print('Dumping Abilities')
    _get_abilities(game_path, version)
    _pullup_data(version, abilities_out_path)

    # csv_items = StringIO()
    # writer = csv.writer(csv_items)
    # writer.writerow(['id', 'slug'])
    # for ability_id, ability_slug in ability_slugs.items():
    #     writer.writerow(['0x{id:02x}'.format(id=ability_id), ability_slug])
    # print(csv_items.getvalue())

    return out, ability_slugs


def _get_abilities(game_path: Path, version: Version):
    num_abilities = 77
    first_ability_name = 0x0C1D
    first_ability_flavor = 0x0CE5
    name_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.COMMON_REL,
        Version.XD: XdStrings.StringTable.COMMON_REL,
    }
    name_table = name_table[version]
    flavor_table = {
        Version.COLOSSEUM: ColoStrings.StringTable.COMMON_REL,
        Version.XD: XdStrings.StringTable.COMMON_REL,
    }
    flavor_table = flavor_table[version]

    for ability_id in range(1, num_abilities + 1):
        name = get_string(game_path, name_table, first_ability_name + (ability_id - 1))
        slug = slugify(name)
        if slug == 'cacophony':
            # Unused ability
            continue
        flavor = get_string(game_path, flavor_table, first_ability_flavor + (ability_id - 1))

        ability_slugs[ability_id] = slug
        out[slug] = {
            'name': name,
            'flavor_text': flavor,
        }


def _pullup_data(version: Version, abilities_out_path: Path):
    pullup_keys = [
        'short_description',
        'description',
    ]
    print('Using existing data')
    for ability_slug in progressbar.progressbar(ability_slugs.values()):
        if ability_slug in ability_name_changes:
            yaml_path = abilities_out_path.joinpath('{ability}.yaml'.format(ability=ability_name_changes[ability_slug]))
        else:
            yaml_path = abilities_out_path.joinpath('{ability}.yaml'.format(ability=ability_slug))
        with yaml_path.open('r') as ability_yaml:
            old_ability_data = yaml.load(ability_yaml.read())
            if version.value not in old_ability_data:
                # If the name has changed, try the original name, as it may have been moved already.
                if ability_slug in ability_name_changes:
                    yaml_path = abilities_out_path.joinpath('{ability}.yaml'.format(ability=ability_slug))
                    with yaml_path.open('r') as ability_yaml:
                        old_ability_data = yaml.load(ability_yaml.read())
                else:
                    raise Exception(
                        'Ability {ability} has no data for version group {version_group}.'.format(
                            ability=ability_slug,
                            version_group=version.value))
            for key in pullup_keys:
                if key in old_ability_data[version.value]:
                    out[ability_slug][key] = old_ability_data[version.value][key]


def write_abilities(out_data: dict, abilities_out_path: Path):
    print('Writing Abilities')
    used_version_groups = set()
    for ability_slug, ability_data in progressbar.progressbar(out_data.items()):
        yaml_path = abilities_out_path.joinpath('{slug}.yaml'.format(slug=ability_slug))
        try:
            with yaml_path.open('r') as ability_yaml:
                data = yaml.load(ability_yaml.read())
        except IOError:
            data = {}
        data.update(ability_data)
        used_version_groups.update(ability_data.keys())
        with yaml_path.open('w') as ability_yaml:
            yaml.dump(data, ability_yaml)

    # Remove this version group's data from extra files
    for yaml_path in progressbar.progressbar(abilities_out_path.iterdir()):
        if yaml_path.suffix != '.yaml':
            continue
        if yaml_path.stem in out_data.keys():
            continue

        # This version group doesn't contain the ability in this file.
        # Remove it if present.
        with yaml_path.open('r') as ability_yaml:
            data = yaml.load(ability_yaml.read())
        changed = False
        for check_version_group in used_version_groups:
            if check_version_group in data:
                del data[check_version_group]
                changed = True
        if changed:
            with yaml_path.open('w') as ability_yaml:
                yaml.dump(data, ability_yaml)
