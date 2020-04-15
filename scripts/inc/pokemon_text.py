import codecs


def search(encoding: str):
    if encoding == 'pokemon_gen1':
        return codecs.CodecInfo(Gen1Text().encode, Gen1Text().decode, name=encoding)
    if encoding == 'pokemon_gen2':
        return codecs.CodecInfo(Gen2Text().encode, Gen2Text().decode, name=encoding)
    else:
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
        0x54: "#",
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
    replacements = {
        '#MON': 'POKéMON'
    }

    def decode(self, binary: bytes, errors='ignore'):
        out = []
        for byte in binary:
            if byte == self._eof:
                out.append("\n")
            else:
                out.append(self._decode_table.get(byte, ' '))

        text = ''.join(out)
        for find, replacement in self.replacements.items():
            text.replace(find, replacement)

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


def register():
    codecs.register(search)
