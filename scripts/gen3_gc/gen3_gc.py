import argparse
from pathlib import Path

from gen3_gc import strings
from inc import group_by_version_group
from .enums import Version
from .moves import get_moves, write_moves

if __name__ == '__main__':
    strings.register_codec()


    def to_path(path: str):
        return Path(path)


    # Get config
    argparser = argparse.ArgumentParser(description='Load Colosseum/XD data')
    argparser.add_argument('--colosseum_dir', type=to_path, required=True, help='Colosseum directory')
    argparser.add_argument('--xd_dir', type=to_path, required=True, help='XD directory')
    argparser.add_argument('--version', action='append', type=lambda version: Version(version),
                           choices=list(Version), help='Version slug to dump')

    argparser.add_argument('--out-pokemon', type=to_path, required=True, help='Pokemon YAML file dir')
    argparser.add_argument('--out-pokemon_moves', type=to_path, required=True, help='Pokemon Move CSV file')
    argparser.add_argument('--out-moves', type=to_path, required=True, help='Move YAML file dir')
    argparser.add_argument('--out-contest_effects', type=to_path, required=True, help='Contest Effect YAML file dir')
    argparser.add_argument('--out-items', type=to_path, required=True, help='Item YAML file dir')
    argparser.add_argument('--out-shop_items', type=to_path, required=True, help='Shop Data CSV file')
    argparser.add_argument('--out-locations', type=to_path, required=True, help='Location YAML file dir')
    argparser.add_argument('--out-encounters', type=to_path, required=True, help='Encounter CSV file')
    argparser.add_argument('--out-abilities', type=to_path, required=True, help='Ability YAML file dir')

    argparser.add_argument('--write-pokemon', action='store_true', help='Write Pokemon data')
    argparser.add_argument('--write-pokemon_moves', action='store_true', help='Write Pokemon move data')
    argparser.add_argument('--write-moves', action='store_true', help='Write Move data')
    argparser.add_argument('--write-contest_effects', action='store_true', help='Write Contest Effect data')
    argparser.add_argument('--write-items', action='store_true', help='Write Item data')
    argparser.add_argument('--write-shops', action='store_true', help='Write Shop data')
    argparser.add_argument('--write-shop_items', action='store_true', help='Write Shop items')
    argparser.add_argument('--write-encounters', action='store_true', help='Write Encounter data')
    argparser.add_argument('--write-abilities', action='store_true', help='Write Ability data')
    args = argparser.parse_args()

    paths = {
        Version.COLOSSEUM: args.colosseum_dir,
        Version.XD: args.xd_dir,
    }

    out_moves = {}
    out_contest_effects = {}
    out_items = {}
    out_shops = {}
    out_shop_items = []
    out_abilities = {}
    out_pokemon = {}
    out_pokemon_moves = set()
    out_encounters = []
    dumped_versions = set()

    for version in args.version:
        dump_path = paths[version]
        print('Using version {version}'.format(version=version.value))

        vg_moves, vg_move_slugs = get_moves(dump_path, version, args.out_moves)
        out_moves = group_by_version_group(version.value, vg_moves, out_moves)

        dumped_versions.add(version.value)

    if len(dumped_versions) < len(args.version):
        # Didn't dump all requested versions
        missing_versions = []
        for requested_version in args.version:
            if requested_version not in dumped_versions:
                missing_versions.append(requested_version)
        print('Could not dump these versions because the ROMs were not available: {roms}'.format(
            roms=', '.join(missing_versions)))
        exit(1)
    else:
        if args.write_moves:
            write_moves(out_moves, args.out_moves)
        exit(0)
