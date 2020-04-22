---
schema: item.json
format: yaml
---

# Filename
Item identifier

{{ include:types/identifier }}

# Top-level keys
Version group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## category
[Item Category](item_category.md) identifier.
{{ include:types/identifier }}
:required:

## pocket
[Item Pocket](item_pocket.md) identifier.
{{ include:types/identifier }}

## buy
Item purchase price.  Omit if this item cannot be purchased.

:type: integer
:minimum: 1

## sell
Item sell price, usually half the purchase price.  Omit if this item cannot be
sold.

:type: integer
:minimum: 1

## flags
A list of [Item Flag](item_flag.md) identifiers.  Flags are special attributes
that affect how the game treats this item.

:type: list\[string\]

## fling_effect
[Fling effect](item_fling_effect.md) identifier; i.e. the effect the move
*Fling* has when used by a Pokémon holding this item.

{{ include:types/identifier }}

## fling_power
The power the move *Fling* has when used by a Pokémon holding this item.

:type: integer
:minimum: 0

## machine
TM/HM data; omit if this item is not a machine.

:type: mapping

### type
The type of machine

:type: string
:values: - `TM`
         - `HM`
:required:

### number
Machine number

:type: integer
:minimum: 1
:required:

### move
Identifier for the [Move](move.md) this machine teaches.

{{ include:types/identifier }}
:required:

## berry
Berry data; omit if this item is not a berry.

:type: mapping

### number
Berry number, used for flavor.

:type: integer
:minimum: 1

### firmness
[Berry Firmness](berry_firmness.md) identifier.
{{ include:types/identifier }}

### natural_gift_type
Identifier for the [Type](type.md) the move *Natural Gift* has when used by a
Pokémon holding this item.
{{ include:types/identifier }}

### natural_gift_power
The power the move *Natural Gift* has when used by a Pokémon holding this item.

:type: integer
:minimum: 0

### size
Berry size, in millimeters.

:type: integer
:minimum: 1

### growth_time
The length of one growth stage, in hours.  Berries go through several of these
growth stages before they can be picked, although the number of stages varies by
generation. 

:type: integer
:minimum: 1

### water
The speed at which this Berry dries out the soil as it grows. A higher rate
means the soil dries more quickly.  This is an arbitrary scale.

:type: integer
:minimum: 1

### smoothness
The smoothness of this Berry, used in making Pokéblocks or Poffins.  This is an
arbitrary scale

:type: integer
:minimum: 1

### flavors
A mapping of Berry flavor intensity.  The keys are [Berry flavor](berry_flavor.md)
identifiers, the values are flavor levels.

:type: mapping\[string: integer\] 
:values: :minimum: 1

### harvest
The number of berries that can grow on one tree.

{{ include:types/range }}
:minimum: 1

### flavor_text
The game displays this on the "tag".

{{ include:types/flavor_text }}

## short_description
{{ include:types/short_description }}

## description
{{ include:types/description }}

## flavor_text
{{ include:types/flavor_text }}

## icon
{{ include:types/asset_path }}
