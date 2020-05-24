import argparse
from dataclasses import dataclass
from pathlib import Path
import sys
from typing import List, Union

from tabulate import tabulate

from gen3_gc.strings import register_codec


@dataclass()
class SearchResult:
    path: Path = None
    address: int = None


def _search_dir(path: Path, needle: Union[str, bytes]) -> List[SearchResult]:
    found: List[SearchResult] = []
    for child in path.iterdir():
        found.extend(search(child, needle))

    return found


def _search_file(path: Path, needle: Union[str, bytes]) -> List[SearchResult]:
    found: List[SearchResult] = []
    if isinstance(needle, str):
        needle_binary = needle.encode('pokemon_colo_xd')
    else:
        needle_binary = needle
    with path.open('rb') as file:
        contents = file.read()
        position = 0
        while position >= 0:
            position = contents.find(needle_binary, position)
            if position >= 0:
                found.append(SearchResult(path=path, address=position))
                position += len(needle)

    return found


def search(path: Path, needle: Union[str, bytes]):
    found: List[SearchResult] = []
    if path.is_dir():
        found.extend(_search_dir(path, needle))
    elif path.is_file():
        found.extend(_search_file(path, needle))

    return found


if __name__ == '__main__':
    register_codec()

    arg_parser = argparse.ArgumentParser()
    arg_parser.add_argument('path', type=lambda path: Path(path), help='The path to search.')
    needle_arg_group = arg_parser.add_mutually_exclusive_group(required=True)
    needle_arg_group.add_argument('--string', type=str, help='String to search for.')
    needle_arg_group.add_argument('--bytes', type=lambda hex_string: bytes.fromhex(hex_string),
                                  help='Hex bytes to search for.')
    args = arg_parser.parse_args()

    path = args.path
    if args.string:
        needle = args.string
    else:
        needle = args.bytes

    found = search(Path(path), needle)
    if isinstance(needle, str):
        needle_display = needle
    else:
        needle_display = needle.hex()
    if len(found) > 0:
        table = []
        for result in found:
            table.append({
                'Path': result.path.relative_to(path),
                'Address': hex(result.address)
            })
        print('Found {number} results for "{needle}":'.format(number=len(found), needle=needle_display))
        print()
        print(tabulate(table, headers='keys'))
    else:
        print('No results found for "{needle}".'.format(needle=needle_display))
