# Use a dump from pk3ds to create encounter data
# https://github.com/kwsch/pk3DS
#
# Usage: encounter_oras.py <version> <Veekun SQLite path> <encounter dump file.txt> <output.csv>
import csv
import re
import sqlite3
import sys
from typing import Any, Dict, List, Optional

from slugify import slugify


class BadLineFormat(Exception):

    def __init__(self, line: int, text: str) -> None:
        super().__init__('Bad format at line {num}: "{line}"'.format(num=line, line=text))


_forms = {
    'Unown': {0: 'A', 1: 'B', 2: 'C', 3: 'D', 4: 'E', 5: 'F', 6: 'G', 7: 'H', 8: 'I', 9: 'J', 10: 'K', 11: 'L', 12: 'M',
              13: 'N', 14: 'O', 15: 'P', 16: 'Q', 17: 'R', 18: 'S', 19: 'T', 20: 'U', 21: 'V', 22: 'W', 23: 'X',
              24: 'Y', 25: 'Z', 26: '!', 27: '?'},
    'Castform': {0: 'Normal', 1: 'Sunny', 2: 'Rainy', 3: 'Snowy'},
    'Deoxys': {0: 'Normal', 1: 'Attack', 2: 'Defense', 3: 'Speed'},
    'Burmy': {0: 'Plant Cloak', 1: 'Sandy Cloak', 2: 'Trash Cloak'},
    'Wormadam': {0: 'Plant Cloak', 1: 'Sandy Cloak', 2: 'Trash Cloak'},
    'Cherrim': {0: 'Overcast', 1: 'Sunshine'},
    'Shellos': {0: 'West Sea', 1: 'East Sea'},
    'Gastrodon': {0: 'West Sea', 1: 'East Sea'},
    'Rotom': {0: 'Normal', 1: 'Heat', 2: 'Wash', 3: 'Frost', 4: 'Fan', 5: 'Mow'},
    'Giratina': {0: 'Altered', 1: 'Origin'},
    'Shaymin': {0: 'Land', 1: 'Sky'},
    'Arceus': {0: 'Normal', 1: 'Fighting', 2: 'Flying', 3: 'Poison', 4: 'Ground', 5: 'Rock', 6: 'Bug', 7: 'Ghost',
               8: 'Steel', 9: 'Fire', 10: 'Water', 11: 'Grass', 12: 'Electric', 13: 'Psychic', 14: 'Ice', 15: 'Dragon',
               16: 'Dark', 17: 'Fairy'},
    'Basculin-Red': {0: 'Striped'},
    'Basculin-Blue': {1: 'Striped'},
    'Darmanitan': {0: 'Standard Mode', 1: 'Zen Mode'},
    'Deerling': {0: 'Spring', 1: 'Summer', 2: 'Autumn', 3: 'Winter'},
    'Sawsbuck': {0: 'Spring', 1: 'Summer', 2: 'Autumn', 3: 'Winter'},
    'Tornadus': {0: 'Incarnate', 1: 'Therian'},
    'Thundurus': {0: 'Incarnate', 1: 'Therian'},
    'Landorus': {0: 'Incarnate', 1: 'Therian'},
    'Kyurem': {0: 'Normal', 1: 'White', 2: 'Black'},
    'Keldeo': {0: 'Ordinary', 1: 'Resolute'},
    'Meloetta': {0: 'Aria', 1: 'Pirouette'},
    'Genesect': {0: 'Normal', 1: 'Water', 2: 'Electric', 3: 'Fire', 4: 'Ice'},
    'Flabebe': {0: 'Red', 1: 'Yellow', 2: 'Orange', 3: 'Blue', 4: 'White'},
    'Floette': {0: 'Red', 1: 'Yellow', 2: 'Orange', 3: 'Blue', 4: 'White', 5: 'Eternal'},
    'Florges': {0: 'Red', 1: 'Yellow', 2: 'Orange', 3: 'Blue', 4: 'White'},
    'Furfrou': {0: 'Natural', 1: 'Heart', 2: 'Star', 3: 'Diamond', 4: 'Deputante', 5: 'Matron', 6: 'Dandy',
                7: 'La Reine', 8: 'Kabuki', 9: 'Pharaoh'},
    'Aegislash': {0: 'Blade'},
    'Vivillon': {0: 'Icy Snow', 1: 'Polar', 2: 'Tundra', 3: 'Continental', 4: 'Garden', 5: 'Elegant', 6: 'Meadow',
                 7: 'Modern', 8: 'Marine', 9: 'Archipelago', 11: 'Sandstorm', 12: 'River', 13: 'Monsoon',
                 14: 'Savannah', 15: 'Sun', 16: 'Ocean', 17: 'Jungle', 18: 'Fancy', 19: 'Poké Ball'},
    'Vivillon-High': {10: 'Plains'},
    'Pumpkaboo': {0: 'Small', 1: 'Average', 2: 'Large', 3: 'Super'},
    'Gourgeist': {0: 'Small', 1: 'Average', 2: 'Large', 3: 'Super'},
    'Hoopa': {0: 'Confined', 1: 'Unbound'},
    'Megas': {0: 'Normal', 1: 'Mega (X)', 2: 'Mega (Y)'}}

# Maps the methods used in pk3ds to method slugs we use
_method_map = {
    'Grass': 'walk',
    'Tall Grass': 'dark-grass',
    'Rock Smash': 'rock-smash',
    # 9/10 times horde/swarm will be in grass, but can also be other things depending on the map and Pokemon.  Will
    # require manual adjustment.
    'Swarm': 'dexnav-???',
    'Horde': 'horde-???',
    'Old Rod': 'old-rod',
    'Good Rod': 'good-rod',
    'Super Rod': 'super-rod',
    'Surf': 'surf'
}

_veekun = sqlite3.connect(sys.argv[2])


def _lookup_pokemon(name: str) -> str:
    c = _veekun.cursor()
    c.execute('''
SELECT "ps"."identifier"
FROM "pokemon_species" "ps"
         JOIN "pokemon_species_names" "psn" ON "ps"."id" = "psn"."pokemon_species_id"
WHERE "psn"."local_language_id" = 9
  AND "psn"."name" = ?
LIMIT 1;
    ''', (name,))
    identifier = c.fetchone()
    if identifier is None:
        raise Exception('Could not find identifier for pokemon "{pokemon}"'.format(pokemon=name))
    return identifier[0]


def _parse_encounters(line_num: int, line: str, map_id: int, map_name: str, method: str, chance: Optional[int]) \
        -> List[Dict[str, Any]]:
    is_horde = chance is not None
    encounters = []
    parts = line.split('),')
    for part in parts:
        part = part.strip()
        # The closing paren may be left out because of splitting
        if not part.endswith(')'):
            part += ')'

        encounter = {
            'version': sys.argv[1],
            'map_id': map_id,
            'location': slugify(map_name),
            'area': 'whole-area',
            'method': _method_map[method],
            'species': None,
            'pokemon': None,
            'level': None,
            'chance': None,
            'conditions': None,
            'note': None
        }
        if not is_horde:
            match = re.match(
                '^(?P<chance>[\d?]+)% - (?P<species>.+?) \(Level (?P<level>\d+), Forme: (?P<form>\d+)\)$', part)
        else:
            match = re.match('^(?P<species>.+?) \(Level (?P<level>\d+), Forme: (?P<form>\d+)\)$', part)

        if not match:
            raise BadLineFormat(line_num, line)

        species = match.group('species')
        form = int(match.group('form'))

        # The species "---" is used to fill empty slots.  Ignore those.
        if species == '---':
            continue

        encounter['species'] = _lookup_pokemon(species)
        try:
            encounter['pokemon'] = slugify(_forms[species][form])
        except KeyError:
            encounter['pokemon'] = encounter['species']

        encounter['level'] = match.group('level')

        # Fill the chance and note as appropriate
        if not is_horde:
            if match.group('chance') == '?':
                # This is a hidden pokemon that appears after Groundon/Kyogre have been defeated
                encounter['chance'] = None
                encounter['note'] = 'Appears only as a [hidden Pokémon]{mechanic:hidden_pokemon} after ' \
                                    '[]{pokemon:groudon}/[]{pokemon:kyogre} have been defeated'
            else:
                encounter['chance'] = int(match.group('chance'))
        else:
            # Horde encounters have a chance set for all members of the horde (because they are encountered together)
            # This makes sure the pokemon's chances all add up to the horde encounter's chance.
            encounter['chance'] = round(chance / len(parts))
            encounter['note'] = 'Part of a [horde]{{mechanic:horde}} encounter ({chance}% chance)'.format(chance=chance)

        encounters.append(encounter)

    return encounters


out = []
f = open(sys.argv[3], 'rt')

line_num = 0
map_id = None
map_name = None
while True:
    line_num += 1
    line = f.readline()
    if line == '':
        # End of file
        break
    if line.strip() == '':
        # Only whitespace
        continue
    line = line.strip()

    # New map
    # Maps are in a header formatted like this:
    # ======
    # Map <three digit id> - <Map name>
    # ======
    map_delim = '======'
    if line == map_delim:
        line_num += 1
        line = f.readline().strip()

        match = re.match('^Map (?P<map_id>\d+) - (?P<map_name>.+)$', line)
        if not match:
            raise BadLineFormat(line_num, line)
        map_id = int(match.group('map_id'))
        map_name = match.group('map_name')

        # Sanity check this is actually a map
        line_num += 1
        assert f.readline().strip() == map_delim

    # No encounters
    elif line == 'No encounters found.':
        continue

    # The remaining possibilities are all encounters.  Each line is a different method.  Most are formatted like this:
    # <Method>: <chance or "?">% <Species> (Level <level number>, Forme: <form id>), ...
    # Horde encounters look like this:
    # Horde <A/B/C> (<chance>%): <Species> (Level <level number>, Forme: <form id>), ...
    # If no encounter is in that slot, the species is "---" and the encounter should be ignored.
    else:
        match = re.match('^(?P<method>.+?): (?P<encounters>.+)$', line)
        if not match:
            raise BadLineFormat(line_num, line)
        method = match.group('method')
        method_encounters = match.group('encounters')

        horde_match = re.match('^Horde \w \((?P<chance>\d+)%\)$', method)
        if horde_match:
            # Hordes need special handling
            chance = int(horde_match.group('chance'))
            method = 'Horde'
        else:
            chance = None
        out.extend(_parse_encounters(line_num, method_encounters, map_id, map_name, method, chance))

# Write the encounters to a csv for further processing
with open(sys.argv[4], 'w') as outfile:
    writer = csv.DictWriter(outfile, fieldnames=out[0].keys())
    writer.writeheader()
    writer.writerows(out)
    outfile.close()
