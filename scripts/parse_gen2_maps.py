import re
import sys

from slugify import slugify

infile = open(sys.argv[1], 'r')
outfile = open(sys.argv[2], 'w')

map_group = 0
map_id = 0

map_slugs = {}
for line in infile:
    match_newgroup = re.match('^\s*newgroup\s*;\s*(?P<group_id>\d+)$', line)
    match_map = re.match('^\s*map_const (?P<map_const>.+),\s*(?P<width>\d+),\s*(?P<height>\d+)\s*;\s*(?P<map_id>\d+)$',
                         line)
    # One line should not match both of these
    assert match_newgroup is None or match_map is None
    if match_newgroup:
        map_group = int(match_newgroup.group('group_id'))
        map_slugs[map_group] = {}
        continue
    elif match_map is None:
        continue

    map_id = int(match_map.group('map_id'))
    map_name = slugify(match_map.group('map_const'))
    map_slugs[map_group][map_id] = {
        'location': map_name,
        'area': 'whole-area',
    }

# This is the world's worst encoder
outfile.write('map_slugs = {}\n')
for group_id, maps in map_slugs.items():
    outfile.write('map_slugs[0x{group:02X}] = {{\n'.format(group=group_id))
    for map_id, data in maps.items():
        strings = []
        for key, value in data.items():
            strings.append('\'{key}\': \'{value}\''.format(key=key, value=value))

        outfile.write('    0x{map:02X}: {{{values}}},\n'.format(map=map_id, values=', '.join(strings)))
    outfile.write('}\n')
