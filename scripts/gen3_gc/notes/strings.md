String Tables
=============

Lots of info borrowed from [this page](https://projectpokemon.org/tutorials/rom/stars-pok%C3%A9mon-colosseum-and-xd-hacking-tutorial/part-2-text-editing-r6/)
and distilled below.

Every file may have one or more string tables, but all tables follow this format.

The tables are double-linked lists.  The previous/next tables are not visible without
the program loaded.

Table Header
------------
Table header is 16 bytes long.

Offset | Length | Value
-------|--------|------
`0x00` | 4      | ???
`0x04` | 2      | Number of entries
`0x06` | 2      | Language code (US, FR, GE, IT, SP, UK)
`0x08` | 4      | Next table address (0x00 until program loaded)
`0x0C` | 4      | Previous table address (0x00 until program loaded)

String Pointers
---------------
Each pointer is 8 bytes long.  The first 4 bytes are a uint32 with the string ID.
The second 4 bytes is the offset of the string from the start of the table.

The strings are indexed and stored in a random order.

Encoding
--------
All strings are encoded (mostly) in UTF-16.  There are some special control codes
starting with `0xFFFF`

Code   | Param length | Value
-------|--------------|------
`0x00` | 0            | Newline
`0x03` | 0            | Newline
`0x07` | 1            | ???
`0x08` | 4            | Change text color (Params are RGBA)
`0x38` | 1            | Change text color (Param uses a preset color, below)
`0x53` | 1            | ???

### Preset colors
Code   | Color
-------|------
`0x00` | White
`0x01` | Yellow
`0x02` | Green
`0x03` | Dark blue
`0x04` | Orange
`0x05` | Black
