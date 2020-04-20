import codecs
from typing import Optional

version: Optional[str] = None


def search(encoding: str):
    if encoding == 'pokemon_gen1':
        return codecs.CodecInfo(Gen1Text().encode, Gen1Text().decode, name=encoding)
    if encoding == 'pokemon_gen2':
        return codecs.CodecInfo(Gen2Text().encode, Gen2Text().decode, name=encoding)
    if encoding == 'pokemon_gen3':
        return codecs.CodecInfo(Gen3Text().encode, Gen3Text().decode, name=encoding)

    return None


def _findval(table: dict, search: str):
    for value, char in table.items():
        if char == search:
            return value

    return None


class Gen1Text(codecs.Codec):
    """
    Decoder for Pokemon Generation I text
    """

    _eof = 0x50
    _decode_table = {
        # These are control characters
        0x49: "\n",
        0x4E: "\n",
        0x5f: '.',

        0x4A: "PkMn",
        0x50: " ",
        0x52: "<PLAYER>",
        0x53: "<RIVAL>",
        0x54: "POKé",
        0x59: "<TARGET>",
        0x5A: "<USER>",
        0x71: "′",
        0x73: "″",
        0x74: "№",
        0x75: "…",
        0x79: "┌",
        0x7A: "─",
        0x7B: "┐",
        0x7C: "│",
        0x7D: "└",
        0x7E: "┘",
        0x7F: " ",
        0x80: "A",
        0x81: "B",
        0x82: "C",
        0x83: "D",
        0x84: "E",
        0x85: "F",
        0x86: "G",
        0x87: "H",
        0x88: "I",
        0x89: "J",
        0x8A: "K",
        0x8B: "L",
        0x8C: "M",
        0x8D: "N",
        0x8E: "O",
        0x8F: "P",
        0x90: "Q",
        0x91: "R",
        0x92: "S",
        0x93: "T",
        0x94: "U",
        0x95: "V",
        0x96: "W",
        0x97: "X",
        0x98: "Y",
        0x99: "Z",
        0x9A: "(",
        0x9B: ")",
        0x9C: ":",
        0x9D: ";",
        0x9E: "[",
        0x9F: "]",
        0xA0: "a",
        0xA1: "b",
        0xA2: "c",
        0xA3: "d",
        0xA4: "e",
        0xA5: "f",
        0xA6: "g",
        0xA7: "h",
        0xA8: "i",
        0xA9: "j",
        0xAA: "k",
        0xAB: "l",
        0xAC: "m",
        0xAD: "n",
        0xAE: "o",
        0xAF: "p",
        0xB0: "q",
        0xB1: "r",
        0xB2: "s",
        0xB3: "t",
        0xB4: "u",
        0xB5: "v",
        0xB6: "w",
        0xB7: "x",
        0xB8: "y",
        0xB9: "z",
        0xBA: "é",
        0xBB: "'d",
        0xBC: "'l",
        0xBD: "'s",
        0xBE: "'t",
        0xBF: "'v",
        0xE0: "'",
        0xE3: "-",
        0xE4: "'r",
        0xE5: "'m",
        0xE6: "?",
        0xE7: "!",
        0xE8: ".",
        0xEC: "▷",
        0xED: "▶",
        0xEE: "▼",
        0xEF: "♂",
        0xF0: "¥",
        0xF1: "×",
        0xF2: "⠄",
        0xF3: "/",
        0xF4: ",",
        0xF5: "♀",
        0xF6: "0",
        0xF7: "1",
        0xF8: "2",
        0xF9: "3",
        0xFA: "4",
        0xFB: "5",
        0xFC: "6",
        0xFD: "7",
        0xFE: "8",
        0xFF: "9",
    }

    def decode(self, binary: bytes, errors='ignore'):
        out = []
        for byte in binary:
            if byte == self._eof:
                out.append("\n")
            else:
                out.append(self._decode_table.get(byte, ' '))

        text = ''.join(out)

        return text, len(binary)

    def encode(self, text: str, errors='ignore'):
        # Because the text table includes single values for multiple characters, this is more complicated than it
        # should be.
        out = bytearray()
        textlen = len(text)

        while len(text) > 0:
            check = text
            while len(check) > 0:
                val = _findval(self._decode_table, check)
                if val:
                    out.append(val)
                    break

                if len(check) == 1:
                    # Can't find this value, insert a space
                    out.append(0x7F)
                    break
                else:
                    # Try again with one character off the end of the sequence
                    check = check[0:-1]

            text = text[len(check):]

        out.append(self._eof)

        return bytes(out), textlen


class Gen2Text(codecs.Codec):
    """
    Decoder for Pokemon Generation II text
    """

    _eof = 0x50
    _decode_table = {
        0x00: "<START>",
        # 0x14: "<PLAY_G>",
        0x15: "<DAY>",
        0x1F: "¯",
        0x22: "\n",
        0x24: "POKé",
        0x25: "%",
        0x38: "<RED>",
        0x39: "<GREEN>",
        0x3F: "<ENEMY>",
        0x49: "<MOM>",
        0x4A: "PkMn",
        0x4B: "<_CONT>",
        0x4C: "<SCROLL>",
        0x4E: "\n",
        0x4F: "\n",

        0x50: " ",
        0x51: "\n",
        0x52: "<PLAYER>",
        0x53: "<RIVAL>",
        0x54: "POKé",
        0x55: "\n",
        0x56: "……",
        0x57: "<DONE>",
        0x58: "<PROMPT>",
        0x59: "<TARGET>",
        0x5A: "<USER>",
        0x5B: "<PC>",
        0x5C: "<TM>",
        0x5D: "<TRNER>",
        0x5E: "<ROCKET>",
        0x5F: "\n",

        0x61: "▲",
        0x62: "_",
        0x6D: ":",
        0x6E: "′",
        0x6F: "″",

        0x70: "Po",
        0x71: "Ké",
        0x72: "``",
        0x73: "''",
        0x74: "№",
        0x75: "…",

        0x79: "┌",
        0x7A: "─",
        0x7B: "┐",
        0x7C: "│",
        0x7D: "└",
        0x7E: "┘",
        0x7F: " ",

        0x80: "A",
        0x81: "B",
        0x82: "C",
        0x83: "D",
        0x84: "E",
        0x85: "F",
        0x86: "G",
        0x87: "H",
        0x88: "I",
        0x89: "J",
        0x8A: "K",
        0x8B: "L",
        0x8C: "M",
        0x8D: "N",
        0x8E: "O",
        0x8F: "P",
        0x90: "Q",
        0x91: "R",
        0x92: "S",
        0x93: "T",
        0x94: "U",
        0x95: "V",
        0x96: "W",
        0x97: "X",
        0x98: "Y",
        0x99: "Z",

        0x9A: "(",
        0x9B: ")",
        0x9C: ":",
        0x9D: ";",
        0x9E: "[",
        0x9F: "]",

        0xA0: "a",
        0xA1: "b",
        0xA2: "c",
        0xA3: "d",
        0xA4: "e",
        0xA5: "f",
        0xA6: "g",
        0xA7: "h",
        0xA8: "i",
        0xA9: "j",
        0xAA: "k",
        0xAB: "l",
        0xAC: "m",
        0xAD: "n",
        0xAE: "o",
        0xAF: "p",
        0xB0: "q",
        0xB1: "r",
        0xB2: "s",
        0xB3: "t",
        0xB4: "u",
        0xB5: "v",
        0xB6: "w",
        0xB7: "x",
        0xB8: "y",
        0xB9: "z",

        0xC0: "Ä",
        0xC1: "Ö",
        0xC2: "Ü",
        0xC3: "ä",
        0xC4: "ö",
        0xC5: "ü",

        0xD0: "'d",
        0xD1: "'l",
        0xD2: "'m",
        0xD3: "'r",
        0xD4: "'s",
        0xD5: "'t",
        0xD6: "'v",

        0xDF: "←",
        0xE0: "'",
        0xE1: "Pk",
        0xE2: "Mn",
        0xE3: "-",

        0xE6: "?",
        0xE7: "!",
        0xE8: ".",
        0xE9: "&",

        0xEA: "é",
        0xEB: "→",
        0xEC: "▷",
        0xED: "▶",
        0xEE: "▼",
        0xEF: "♂",
        0xF0: "$",
        0xF1: "×",
        0xF2: "·",
        0xF3: "/",
        0xF4: ",",
        0xF5: "♀",

        0xF6: "0",
        0xF7: "1",
        0xF8: "2",
        0xF9: "3",
        0xFA: "4",
        0xFB: "5",
        0xFC: "6",
        0xFD: "7",
        0xFE: "8",
        0xFF: "9",
    }

    def decode(self, binary: bytes, errors='ignore'):
        out = []
        for byte in binary:
            if byte == self._eof:
                out.append("\n")
            else:
                out.append(self._decode_table.get(byte, ' '))

        text = ''.join(out)

        return text, len(binary)

    def encode(self, text: str, errors='ignore'):
        # Because the text table includes single values for multiple characters, this is more complicated than it
        # should be.
        out = bytearray()
        textlen = len(text)

        while len(text) > 0:
            check = text
            while len(check) > 0:
                if check == ' ':
                    # Spaces are always this value
                    out.append(0x7F)
                    break

                val = _findval(self._decode_table, check)
                if val:
                    out.append(val)
                    break

                if len(check) == 1:
                    # Can't find this value, insert a space
                    out.append(0x7F)
                    break
                else:
                    # Try again with one character off the end of the sequence
                    check = check[0:-1]

            text = text[len(check):]

        out.append(self._eof)

        return bytes(out), textlen


class Gen3Text(codecs.Codec):
    """
    Decoder for Pokemon Generation III text
    """
    _eof = 0xFF
    _decode_table = {
        0x00: ' ',
        0x01: 'À',
        0x02: 'Á',
        0x03: 'Â',
        0x04: 'Ç',
        0x05: 'È',
        0x06: 'É',
        0x07: 'Ê',
        0x08: 'Ë',
        0x09: 'Ì',
        0x0B: 'Î',
        0x0C: 'Ï',
        0x0D: 'Ò',
        0x0E: 'Ó',
        0x0F: 'Ô',
        0x10: 'Œ',
        0x11: 'Ù',
        0x12: 'Ú',
        0x13: 'Û',
        0x14: 'Ñ',
        0x15: 'ß',
        0x16: 'à',
        0x17: 'á',
        0x19: 'ç',
        0x1A: 'è',
        0x1B: 'é',
        0x1C: 'ê',
        0x1D: 'ë',
        0x1E: 'ì',
        0x20: 'î',
        0x21: 'ï',
        0x22: 'ò',
        0x23: 'ó',
        0x24: 'ô',
        0x25: 'œ',
        0x26: 'ù',
        0x27: 'ú',
        0x28: 'û',
        0x29: 'ñ',
        0x2A: 'º',
        0x2B: 'ª',
        0x2D: '&',
        0x2E: '+',
        0x34: 'Lv',
        0x35: '=',
        0x36: ';',
        0x51: '¿',
        0x52: '¡',
        0x53: 'Pk',
        0x54: 'Mn',
        0x5A: 'Í',
        0x5B: '%',
        0x5C: '(',
        0x5D: ')',
        0x68: 'â',
        0x6F: 'í',
        0x79: '⬆',
        0x7A: '⬇',
        0x7B: '⬅',
        0x7C: '➡',
        0x85: '<',
        0x86: '>',
        0xA1: '0',
        0xA2: '1',
        0xA3: '2',
        0xA4: '3',
        0xA5: '4',
        0xA6: '5',
        0xA7: '6',
        0xA8: '7',
        0xA9: '8',
        0xAA: '9',
        0xAB: '!',
        0xAC: '?',
        0xAD: '.',
        0xAE: '-',
        0xB0: '…',
        0xB1: '“',
        0xB2: '”',
        0xB3: '‘',
        0xB4: '\'',
        0xB5: '♂',
        0xB6: '♀',
        0xB7: '$',
        0xB8: ',',
        0xB9: '×',
        0xBA: '/',
        0xBB: 'A',
        0xBC: 'B',
        0xBD: 'C',
        0xBE: 'D',
        0xBF: 'E',
        0xC0: 'F',
        0xC1: 'G',
        0xC2: 'H',
        0xC3: 'I',
        0xC4: 'J',
        0xC5: 'K',
        0xC6: 'L',
        0xC7: 'M',
        0xC8: 'N',
        0xC9: 'O',
        0xCA: 'P',
        0xCB: 'Q',
        0xCC: 'R',
        0xCD: 'S',
        0xCE: 'T',
        0xCF: 'U',
        0xD0: 'V',
        0xD1: 'W',
        0xD2: 'X',
        0xD3: 'Y',
        0xD4: 'Z',
        0xD5: 'a',
        0xD6: 'b',
        0xD7: 'c',
        0xD8: 'd',
        0xD9: 'e',
        0xDA: 'f',
        0xDB: 'g',
        0xDC: 'h',
        0xDD: 'i',
        0xDE: 'j',
        0xDF: 'k',
        0xE0: 'l',
        0xE1: 'm',
        0xE2: 'n',
        0xE3: 'o',
        0xE4: 'p',
        0xE5: 'q',
        0xE6: 'r',
        0xE7: 's',
        0xE8: 't',
        0xE9: 'u',
        0xEA: 'v',
        0xEB: 'w',
        0xEC: 'x',
        0xED: 'y',
        0xEE: 'z',
        0xEF: '▶',
        0xF0: ':',
        0xF1: 'Ä',
        0xF2: 'Ö',
        0xF3: 'Ü',
        0xF4: 'ä',
        0xF5: 'ö',
        0xF6: 'ü',
        0xFA: '\n',
        0xFB: '\n',
        0xFE: '\n',
        0xFF: '\n',
    }
    # Control characters are in two groups, FC## and FD##
    # FD## contains many version-specific values that are filled in the constructor
    _control = {
        0xFC: {
            0x00: '',  # End of a town/city name (before " TOWN" or " CITY")
        },
        0xFD: {
            0x01: '<Player>',
            0x06: '<Rival>',
        }
    }
    # Some control codes use several bytes after that we ignore.
    # Values not listed here have no additional bytes.
    _control_length = {
        0xFC: {
            0x01: 1,
            0x02: 1,
            0x03: 1,
            0x04: 3,
            0x05: 1,
            0x06: 1,
            0x08: 1,
            0x0B: 2,
            0x10: 2,
        },
        0xFD: {}
    }

    def __init__(self):
        # Init version-specific control codes
        if version == 'ruby':
            self._control[0xFD].update({
                0x07: 'RUBY',  # Version
                0x08: 'MAGMA',  # Evil Team
                0x09: 'AQUA',  # Good Team
                0x0A: 'MAXIE',  # Evil Leader
                0x0B: 'ARCHIE',  # Good Leader
                0x0C: 'GROUDON',  # Evil Legendary
                0x0D: 'KYOGRE',  # Good Legendary
            })
        elif version == 'sapphire':
            self._control[0xFD].update({
                0x07: 'SAPPHIRE',  # Version
                0x08: 'AQUA',  # Evil Team
                0x09: 'MAGMA',  # Good Team
                0x0A: 'ARCHIE',  # Evil Leader
                0x0B: 'MAXIE',  # Good Leader
                0x0C: 'KYOGRE',  # Evil Legendary
                0x0D: 'GROUDON',  # Good Legendary
            })

    def decode(self, binary: bytes, errors='strict'):
        out = []
        cursor = 0
        while cursor < len(binary):
            byte = binary[cursor]
            if byte == self._eof:
                out.append("\n")
            elif byte in self._decode_table:
                out.append(self._decode_table.get(byte, ' '))
            elif byte in self._control:
                control = byte
                cursor += 1
                value = binary[cursor]
                if value not in self._control[control]:
                    # Raise an error if errors are on, otherwise ignore the character
                    out.append(self._decode_error(errors, binary, cursor - 1, cursor))
                else:
                    out.append(self._control.get(value))

                if value in self._control_length[control]:
                    # Move past additional control bytes
                    cursor += self._control_length[value]
            else:
                self._decode_error(errors, binary, cursor, cursor)

            cursor += 1

        text = ''.join(out)

        return text, len(binary)

    def _decode_error(self, errors, binary, start, end):
        error_bytes = binary[start:end + 1]
        if errors == 'ignore':
            return '',
        else:
            raise UnicodeDecodeError('pokemon_gen3', binary, start, end,
                                     'The byte 0x{byte} is not a valid character.'.format(byte=hex(error_bytes)))

    def encode(self, text: str, errors='strict'):
        # Because the text table includes single values for multiple characters, this is more complicated than it
        # should be.
        original = text
        out = bytearray()
        textlen = len(original)
        start = 0

        while len(text) > 0:
            check = text
            while len(check) > 0:
                if check == '\n':
                    # Newlines are always this value
                    out.append(0xFE)
                    break

                # Search first in the control substitutions for full strings like "MAGMA",
                # then use the decode table.
                val = _findval(self._control[0xFD], check)
                if val is not None:
                    out.extend([0xFD, val])
                    break
                val = _findval(self._decode_table, check)
                if val is not None:
                    out.append(val)
                    break

                if len(check) == 1:
                    # Can't find this value
                    if errors == 'ignore':
                        break
                    else:
                        raise UnicodeEncodeError('pokemon_gen3', original, start, start,
                                                 'The character "{char}" cannot be converted to the game\'s encoding.'.format(
                                                     char=check))
                else:
                    # Try again with one character off the end of the sequence
                    check = check[0:-1]

            # Continue to the next unencoded part of the string
            start = len(check)
            text = text[start:]

        return bytes(out), textlen


def register(use_version: str = None):
    global version
    version = use_version
    codecs.register(search)
