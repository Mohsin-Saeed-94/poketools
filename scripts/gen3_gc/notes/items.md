Item stats live in main.dol, starting at 0x360D10 in Colosseum and 01FF0C in XD

Names live in common_rel.fdat in the first string table.

Flavor lives in pocket_menu_00000002.fdat in Colosseum, 1 in XD

The struct is 40 bytes long and is the same format in both games.

There are 396 items in Colosseum, 442 in XD.  They are in the same order
as the items from Ruby/Sapphire with the version-specific key items at the
end after the TMs/HMs.

The struct format is:

Offset | Type | Value
-------|------|------
`0x00` | u8   | Item Pocket ID (see below)
`0x06` | u16  | Price
`0x10` | u32  | Name ID
`0x14` | u32  | Flavor text ID
`0x1A` | u16  | Sort order (this starts at 0 for each pocket)

There are LOTS of dummy items; these are easy to find because the name id is 0.
Most of these dummy items were key items in the R/S.

Pocket IDs:
ID     | Name
-------|-----
`0x00` | Dummy
`0x01` | Pokeballs
`0x02` | Items
`0x03` | Berries
`0x04` | Machines
`0x05` | Key
`0x06` | Cologne case
`0x07` | Disc case
