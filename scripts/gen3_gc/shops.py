import csv
import io
from io import BufferedReader
from pathlib import Path
from typing import Any, Dict, List

from .enums import Version


def get_shop_items(game_path: Path, version: Version, item_slugs: dict, items: dict):
    out = []

    shop_table__file_path = game_path.joinpath('pocket_menu.fsys/pocket_menu.fdat')
    shop_table_offset = 0x0002ac
    shop_map = {
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
        11: {'location': 'mt-battle', 'area': 'lobby', 'shop': 'poke-coupon-exchange'}
    }

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
                if item_id >= 0x21F:
                    # Not sure why the scents happen this way, but they are...
                    item_id -= 0x97
                item = item_slugs[item_id]
                if shop_info['shop'] == 'poke-coupon-exchange':
                    buy = None
                else:
                    buy = items[item]['buy']
                out.append({
                    'version_group': version.value,
                    'location': shop_info['location'],
                    'area': shop_info['area'],
                    'shop': shop_info['shop'],
                    'item': item,
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
