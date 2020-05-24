# Parse the groups.inc file from the disassembly project
# to create locations

import re
import sys

from slugify import slugify


def to_snake_case(name: str):
    def _snakeify(match: re.Match):
        return '{before}-{after}'.format(before=match.group(1).lower(), after=match.group(2).lower())

    return re.sub(r'([a-z])([A-Z0-9])', _snakeify, name)


infile = open(sys.argv[1], 'r')
outfile = open(sys.argv[2], 'w')

map_group = 0
map_id = 0

map_slugs = {}
for line in infile:
    match_newgroup = re.match('^gMapGroup(?P<group_id>\d+)::$', line)
    match_map = re.match('^\s*\.4byte (?P<map_name>.+)$', line)
    # One line should not match both of these
    assert match_newgroup is None or match_map is None
    if match_newgroup:
        map_group = int(match_newgroup.group('group_id'))
        map_id = 0
        map_slugs[map_group] = {}
        continue
    elif match_map is None:
        # Blank line, comment, etc.
        continue

    map_name = match_map.group('map_name')
    name_parts = map_name.split('_', maxsplit=2)
    location = slugify(to_snake_case(name_parts[0]))
    location = re.sub(r'^route-', 'hoenn-route-', location)
    if len(name_parts) == 1:
        area = 'whole-area'
    else:
        area_parts = []
        for area_part in name_parts[1:]:
            area_parts.append(slugify(to_snake_case(area_part)))
        area = '/'.join(area_parts)

    map_slugs[map_group][map_id] = {
        'location': location,
        'area': area,
    }
    map_id += 1

# This is the world's worst encoder, but PyCharm will auto-format and fix the result.
outfile.write('map_slugs = {}\n')
for group_id, maps in map_slugs.items():
    outfile.write('map_slugs[0x{group:02X}] = {{\n'.format(group=group_id))
    for map_id, data in maps.items():
        strings = []
        for key, value in data.items():
            strings.append('\'{key}\': \'{value}\''.format(key=key, value=value))

        outfile.write('    0x{map:02X}: {{{values}}},\n'.format(map=map_id, values=', '.join(strings)))
    outfile.write('}\n')
