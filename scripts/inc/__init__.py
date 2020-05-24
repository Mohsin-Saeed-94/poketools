def group_by_version_group(version_group: str, data: dict, update=None):
    """
    Group the data by version group.

    This adds a second level to the input dict before the data, e.g.
    `['pound']['name'] = 'POUND'` becomes `['pound']['ruby-sapphire']['name'] = 'POUND'`

    :param version_group:
    :param data:
    :param update:
    :return:
    """
    if update is None:
        update = {}

    for slug, vg_data in data.items():
        if slug not in update:
            update[slug] = {}
        update[slug][version_group] = vg_data

    return update


def group_pokemon(version: str, version_group: str, data: dict, update=None):
    """
    Similar to group_by_version_group, but some Pokemon data is grouped by version as well.

    :param version:
    :param version_group:
    :param data:
    :param update:
    """
    if update is None:
        update = {}

    version_keys = ['wild_held_items', 'flavor_text']
    for slug, new_data in data.items():
        if slug not in update:
            update[slug] = {}
        if version_group not in update[slug]:
            update[slug][version_group] = {}

        # Handle grouped by version data
        # Store the old data for later reference
        old_pokemon = {}
        if 'pokemon' in update[slug][version_group]:
            for pokemon_slug, old_pokemon_data in update[slug][version_group]['pokemon'].items():
                old_pokemon[pokemon_slug] = old_pokemon_data.copy()

        # Reformat the new data by appending it to the old data
        for pokemon_slug, new_pokemon_data in new_data['pokemon'].items():
            if pokemon_slug not in old_pokemon:
                old_pokemon[pokemon_slug] = {}

            for key in version_keys:
                if key not in new_pokemon_data:
                    continue

                if key not in old_pokemon[pokemon_slug]:
                    old_pokemon[pokemon_slug][key] = {}
                versioned_data = old_pokemon[pokemon_slug][key].copy()
                versioned_data.update({version: new_pokemon_data[key]})
                new_data['pokemon'][pokemon_slug][key] = versioned_data

        # Preserve Pokemon not in the new dataset (this is primarily for Deoxys, which has
        # specific forms in each version).  Doing this earlier would lead to some duplication.
        if 'pokemon' in update[slug][version_group]:
            for pokemon_slug, old_pokemon_data in update[slug][version_group]['pokemon'].items():
                if pokemon_slug not in new_data['pokemon']:
                    new_data['pokemon'][pokemon_slug] = old_pokemon_data

        update[slug][version_group] = new_data

    return update
