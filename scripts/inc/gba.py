def address_from_pointer(pointer: bytes, byteorder='little') -> int:
    """
    Calculate the location in the ROM of a pointer
    :param pointer:
    :param byteorder:
    :return:
    """
    pointer = int.from_bytes(pointer, byteorder=byteorder)
    return pointer - 0x08000000


def pointer_from_address(address: int, byteorder='little') -> bytes:
    """
    Calculate a pointer to be stored in the ROM from an address
    :param address:
    :param byteorder:
    :return:
    """
    return (address + 0x08000000).to_bytes(4, byteorder=byteorder, signed=False)
