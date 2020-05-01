import codecs
from dataclasses import dataclass
import enum
from io import BufferedReader
from pathlib import Path
import struct
from typing import Dict, List


class StringTableBase(enum.Enum):
    pass


@dataclass(frozen=True)
class StringTableInfo:
    path: Path = None
    offsets: List[int] = None


class ColoStrings:
    class StringTable(StringTableBase):
        MAIN = StringTableInfo(path=Path('../sys/main.dol'), offsets=[0x2CC810])
        COMMON_REL = StringTableInfo(path=Path('common.fsys/common_rel.fdat'), offsets=[0x059890, 0x066000, 0x0784E0])


class XdStrings:
    class StringTable(StringTableBase):
        MAIN = StringTableInfo(path=Path('../sys/main.dol'), offsets=[0x374FC0])
        COMMON_REL = StringTableInfo(path=Path('common.fsys/common_rel.fdat'), offsets=[0x04E274])


class _StringTableCache:
    # Stores the absolute address of the string
    # Table -> String ID -> String absolute offset
    _cache: Dict[StringTableBase, Dict[int, int]] = {}

    @staticmethod
    def add_offset(table: StringTableBase, string_id: int, offset: int):
        if not _StringTableCache.has_table(table):
            _StringTableCache._cache[table] = {}

        _StringTableCache._cache[table][string_id] = offset

    @staticmethod
    def get_offset(table: StringTableBase, string_id: int) -> int:
        if not _StringTableCache.has_string_id(table, string_id):
            raise Exception('The string table {table} has no entry for string id {string}.'.format(
                table=table.name, string=string_id))

        return _StringTableCache._cache[table][string_id]

    @staticmethod
    def has_table(table: StringTableBase) -> bool:
        return table in _StringTableCache._cache

    @staticmethod
    def has_string_id(table: StringTableBase, string_id: int) -> bool:
        return table in _StringTableCache._cache and string_id in _StringTableCache._cache[table]


def get_string(root: Path, table: StringTableBase, string_id: int):
    table_info: StringTableInfo = table.value
    file_path = root.joinpath(table_info.path)
    assert file_path.is_file()

    @dataclass()
    class StringTableHeader:
        def __init__(self, data: bytes):
            data = struct.unpack('>4xH2sII', data)
            self.numStrings = data[0]
            self.language = data[1].decode('ascii')
            self.nextTableAddress = data[2]
            self.previousTableAddress = data[3]

    table_file: BufferedReader
    with file_path.open('rb') as table_file:
        # Read the header to get string info
        if not _StringTableCache.has_table(table):
            for offset in table_info.offsets:
                table_file.seek(offset)
                header = StringTableHeader(table_file.read(16))
                for i in range(header.numStrings):
                    table_string_id = int.from_bytes(table_file.read(4), byteorder='big')
                    table_string_pointer = int.from_bytes(table_file.read(4), byteorder='big')
                    _StringTableCache.add_offset(table, table_string_id, table_string_pointer + offset)

        table_file.seek(_StringTableCache.get_offset(table, string_id))
        binary = bytearray()
        while table_file.peek(2)[:2] != bytes.fromhex('00 00'):
            char = table_file.read(2)
            if char == bytes.fromhex('FF FF'):
                # This is a control code, so data is no longer 2 byte characters.  Get past this
                # as this will be parsed properly in the codec.
                binary.extend(char)
                control_code = table_file.read(1)[0]

                if control_code in ColoXdCodec.control:
                    # Control replacements
                    binary.append(control_code)
                elif control_code in ColoXdCodec.control_length:
                    # Control codes with params we don't care about
                    binary.extend(table_file.read(ColoXdCodec.control_length[control_code]))
            else:
                binary.extend(char)
        return binary.decode('pokemon_colo_xd')


class ColoXdCodec(codecs.Codec):
    _base_encoding = 'utf_16_be'

    # Control characters that represent text
    control = {
        0x00: '\n',
        0x03: '\n',
    }

    # Control characters with lengths > 1 (i.e. take params)
    control_length = {
        0x07: 1,
        0x08: 4,
        0x38: 1,
        0x53: 1,
    }

    def encode(self, text: str, errors='ignore'):
        return text.encode(self._base_encoding, errors), len(text)

    def decode(self, binary: bytes, errors='ignore'):
        length = len(binary)
        # Replace control codes
        binary = bytearray(binary)
        while bytes.fromhex('FF FF') in binary:
            parts = binary.partition(bytes.fromhex('FF FF'))
            if len(parts[1]) == 0 and len(parts[2]) == 0:
                break

            control_code = parts[2][0]

            # Control replacements
            if control_code in self.control:
                replaced = self.control[parts[2][0]].encode(self._base_encoding) + parts[2][1:]
                binary = parts[0] + replaced
                continue

            # Control codes with params we don't care about
            if control_code in self.control_length:
                binary = parts[0] + parts[2][self.control_length[control_code] + 1:]
                continue

            # Misc control codes with no params
            binary = parts[0] + parts[2][1:]

        # Pass the binary form to the UTF-16 decoder
        return binary.decode(self._base_encoding, errors), length


_colo_xd_codec = ColoXdCodec()


def codec_search(encoding: str):
    codec_map = {
        'pokemon_colo_xd': _colo_xd_codec,
    }
    if encoding not in codec_map:
        return None

    return codecs.CodecInfo(codec_map[encoding].encode, codec_map[encoding].decode, name=encoding)


def register_codec():
    codecs.register(codec_search)
