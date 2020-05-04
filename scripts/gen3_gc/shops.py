import csv
import io
from io import BufferedReader
from pathlib import Path
from typing import Any, Dict, List

from .enums import Version


def get_shop_items(game_path: Path, version: Version, item_slugs: dict, items: dict):
    out = []

    shop_table__file_path = game_path.joinpath('pocket_menu.fsys/pocket_menu.fdat')
    shop_table_offset = {
        Version.COLOSSEUM: 0x0002ac,
        Version.XD: 0x000300
    }
    shop_table_offset = shop_table_offset[version]
    shop_map = {
        Version.COLOSSEUM: {
            0: {'location': 'outskirt-stand', 'area': 'diner', 'shop': 'clerk-start'},
            1: {'location': 'outskirt-stand', 'area': 'diner', 'shop': 'clerk-rui'},
            2: {'location': 'outskirt-stand', 'area': 'diner', 'shop': 'clerk-duking-email'},
            3: {'location': 'phenac-city', 'area': 'mart', 'shop': 'mart-1f'},
            4: {'location': 'phenac-city', 'area': 'mart', 'shop': 'mart-2f'},
            5: {'location': 'pyrite-town', 'area': 'mart', 'shop': 'mart'},
            6: {'location': 'agate-village', 'area': 'mart', 'shop': 'mart'},
            7: {'location': 'the-under', 'area': 'mart', 'shop': 'mart'},
            8: {'location': 'the-under', 'area': 'herb-shop', 'shop': 'herb-shop'},
            # 9 is an unused shop with only the TMS found in The Under.
            10: {'location': 'the-under', 'area': 'whole-area', 'shop': 'vending-machine'},
            11: {'location': 'mt-battle', 'area': 'lobby', 'shop': 'poke-coupon-exchange'},
        },
        Version.XD: {
            0: {'location': 'realgam-tower', 'area': 'mart', 'shop': 'mart'},
            1: {'location': 'realgam-tower', 'area': 'battle-sim', 'shop': 'battle-cd-1'},
            2: {'location': 'realgam-tower', 'area': 'battle-sim', 'shop': 'battle-cd-2'},
            3: {'location': 'realgam-tower', 'area': 'battle-sim', 'shop': 'battle-cd-3'},
            4: {'location': 'phenac-city', 'area': 'mart', 'shop': 'mart-1f'},
            5: {'location': 'phenac-city', 'area': 'mart', 'shop': 'mart-2f'},
            6: {'location': 'pyrite-town', 'area': 'mart', 'shop': 'mart-start'},
            7: {'location': 'pyrite-town', 'area': 'mart', 'shop': 'mart-onbs'},
            8: {'location': 'pyrite-town', 'area': 'whole-area', 'shop': 'vending-machine'},
            9: {'location': 'agate-village', 'area': 'mart', 'shop': 'mart-start'},
            10: {'location': 'agate-village', 'area': 'mart', 'shop': 'mart-aidan'},
            11: {'location': 'agate-village', 'area': 'mart', 'shop': 'mart-onbs'},
            12: {'location': 'gateon-port', 'area': 'mart', 'shop': 'mart-start'},
            13: {'location': 'gateon-port', 'area': 'mart', 'shop': 'mart-onbs'},
            14: {'location': 'gateon-port', 'area': 'mart', 'shop': 'mart-gorigan'},
            15: {'location': 'gateon-port', 'area': 'herb-shop', 'shop': 'herb-shop'},
            16: {'location': 'outskirt-stand', 'area': 'diner', 'shop': 'clerk'},
            17: {'location': 'mt-battle', 'area': 'lobby', 'shop': 'poke-coupon-exchange'},
        }
    }
    shop_map = shop_map[version]

    print('Dumping shop items')

    shop_table_file: BufferedReader
    with shop_table__file_path.open('rb') as shop_table_file:
        shop_table_file.seek(shop_table_offset)
        last_id = 0
        for shop_id, shop_info in shop_map.items():
            # Shops are stored sequentially but not all are used.  Keep reading until
            # meeting the next shop.
            skip = shop_id - last_id - 1
            while skip > 0:
                while shop_table_file.read(2)[:2] != bytes.fromhex('00 00'):
                    pass
                skip -= 1
            last_id = shop_id

            while shop_table_file.peek(2)[:2] != bytes.fromhex('00 00'):
                item_id = int.from_bytes(shop_table_file.read(2), byteorder='big')
                item_slug = item_slugs[item_id]
                if shop_info['shop'] == 'poke-coupon-exchange':
                    buy = None
                else:
                    buy = items[item_slug]['buy']
                out.append({
                    'version_group': version.value,
                    'location': shop_info['location'],
                    'area': shop_info['area'],
                    'shop': shop_info['shop'],
                    'item': item_slug,
                    'buy': buy,
                })
            shop_table_file.seek(2, io.SEEK_CUR)

    return out


def write_shop_items(used_version_groups, out_data: List[Dict[str, Any]], shop_items_out_path: Path):
    print('Writing shop items')

    data = []
    with shop_items_out_path.open('r') as shop_items_csv:
        for row in csv.DictReader(shop_items_csv):
            if row['version_group'] not in used_version_groups:
                data.append(row)
    data.extend(out_data)

    with shop_items_out_path.open('w') as shop_items_csv:
        writer = csv.DictWriter(shop_items_csv, data[0].keys())
        writer.writeheader()
        writer.writerows(data)
