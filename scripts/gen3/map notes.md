Group Pointers
==============
Version   | Offset     | Group Count
--------- | ---------- | -----------
Ruby      | `0x3085A0` | 34
Sapphire  | `0x307F08` | 34
Emerald   | `0x486578` | 34
FireRed   | `0x3526A8` | 43
LeafGreen | `0x352688` | 43

Header Pointers
===============

Version   | Offset     | First Group
--------- | ---------- | -----------
Ruby      | `0x307F78` | 0
Sapphire  | `0x307f0B` | 0
Emerald   | `0x485D60` | 0
FireRed   | `0x352004` | 0
LeafGreen | `0x351FE4` | 0

Headers
=======

Version   | Offset     | First Map
--------- | ---------- | ---------
Ruby      | `0x305460` | Petalburg
Sapphire  | `0x3053F0` | Petalburg
Emerald   | `0x4824B8` | Petalburg
FireRed   | `0x34F188` | Link Battle Colosseum
LeafGreen | `0x34F168` | Link Battle Colosseum

Map Events format
================
Follow the map's events pointer (bytes 4-7 in the header)
The result is (size 20):

Offset | Value
-----: | -----
`0x00` | # of NPCs
`0x01` | # of warps
`0x02` | # of traps
`0x03` | # of signs
`0x04` | pointer to npc events list
`0x08` | pointer to warp events list
`0x0C` | pointer to trap events list
`0x10` | pointer to signs events list

Object Event format
===================
Follow the npc events pointer.
The result us (size 24 aligned)

Offset (length) | Value
--------------: | -----
`0x00` (1)      | Event id
`0x01` (1)      | Sprite id
`0x02` (1)      | Replacement (?) id
`0x03` (1)      | Padding
`0x04` (2)      | Map X
`0x06` (2)      | Map Y
`0x08` (1)      | Elevation
`0x09` (1)      | Movement Type
`0x0A` (1)      | Radius (?)
`0x0B` (1)      | Padding
`0x0C` (2)      | Trainer Class id
`0x0E` (2)      | Sight Radius
`0x10` (4)      | Pointer to script
`0x14` (2)      | Event flag
`0x16` (2)      | Padding
