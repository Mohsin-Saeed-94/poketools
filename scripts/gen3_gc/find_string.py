from dataclasses import dataclass
from pathlib import Path
import sys
from typing import List

from tabulate import tabulate

from gen3_gc.strings import register_codec


@dataclass()
class SearchResult:
    path: Path = None
    address: int = None


def _search_dir(path: Path, needle: str) -> List[SearchResult]:
    found: List[SearchResult] = []
    for child in path.iterdir():
        found.extend(search(child, needle))

    return found


def _search_file(path: Path, needle: str) -> List[SearchResult]:
    found: List[SearchResult] = []
    needle_binary = needle.encode('pokemon_colo_xd')
    with path.open('rb') as file:
        contents = file.read()
        position = 0
        while position >= 0:
            position = contents.find(needle_binary, position)
            if position >= 0:
                found.append(SearchResult(path=path, address=position))
                position += len(needle)

    return found


def search(path: Path, needle: str):
    found: List[SearchResult] = []
    if path.is_dir():
        found.extend(_search_dir(path, needle))
    elif path.is_file():
        found.extend(_search_file(path, needle))

    return found


if __name__ == '__main__':
    register_codec()

    path = Path(sys.argv[1])
    needle = sys.argv[2]

    found = search(Path(path), needle)
    if len(found) > 0:
        table = []
        for result in found:
            table.append({
                'Path': result.path.relative_to(path),
                'Address': hex(result.address)
            })
        print('Found {number} results for "{needle}":'.format(number=len(found), needle=needle))
        print()
        print(tabulate(table, headers='keys'))
    else:
        print('No results found for "{needle}".'.format(needle=needle))
