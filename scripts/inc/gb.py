from typing import Tuple


def address_from_pointer(pointer: bytes, bank: int = 0) -> int:
    """
    Calculate the location in the ROM of a pointer
    :param pointer:
    :param bank:
    :return:
    """
    pointer = int.from_bytes(pointer, byteorder='little')
    return (bank * 0x4000) + (pointer - 0x4000)


def pointer_from_address(address: int) -> Tuple[bytes, int]:
    """
    Calculate a pointer to be stored in the ROM from an address
    :param address:
    :return:
    """
    pointer = (address % 0x4000) + 0x4000
    bank = address // 0x4000

    return pointer.to_bytes(2, byteorder='little'), bank
