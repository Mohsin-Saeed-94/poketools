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
