---
schema: pokemon.json
format: yaml
---

# Filename
Pokémon identifier

{{ include:types/identifier }}

# Top-level keys
Version group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## position
{{ include:types/position }}
:required:

## numbers
A mapping describing the numbers this species has across the Pokédexes it
appears in.  Keys are [Pokédex](pokedex.md) identifiers, values are numbers.

:type: mapping\[string: integer\]

## pokemon
Pokémon are defined as a form with different types, moves, or other game-
changing properties; e.g. There are four separate "Pokemon" for Deoxys, but
only one for Unown.

Keys are used as identifiers for referencing that specific Pokémon.

:type: mapping
:minimum values: 1
:required:

### name
{{ include:types/name }}
:required:

### genus
The short flavor text, such as "Seed" or "Lizard"; usually affixed with the word
"Pokémon".

:type: string

### color
Identifier for the [Pokémon Color](pokemon_color.md), used for a search function
in the games.
{{ include:types/identifier }}

### shape
Identifier for the [Pokémon Shape](pokemon_shape.md), used for a search function
in the games.
{{ include:types/identifier }}

### habitat
Identifier for the [Pokémon Habitat](pokemon_habitat.md), used for a search
function in the games.
{{ include:types/identifier }}

### female_rate
Percentage of wild encounters that are female.

:type: integer
:minimum: 0
:maximum: 100

### capture_rate
Wild capture rate, used as part of the Pokéball calculations.

:type: integer
:minimum: 1
:maximum: 255
:required:

### baby
Is this a baby (and thus unable to breed)?

:type: boolean
:default: false

### hatch_steps
Number of steps in one egg cycle.  The number of cycles before hatching depends
on the generation.

:type: integer
:minimum: 1

### growth_rate
[Growth Rate](growth_rate.md) identifier.
{{ include:types/identifier }}
:required:

### forms_switchable
Has manually-switchable forms?

:type: boolean
:default: false

### forms_note
A special note about how forms work with the Pokémon.

{{ include:types/markdown }}

### pal_park
Pal Park data, for version groups with the Pal Park.

:type: mapping

#### area
[Pal Park Area](pal_park_area.md) identifier.
{{ include:types/identifier }}
:required:

#### rate
Encounter rate in the Pal Park.

:type: integer
:minimum: 1
:maximum: 100
:required:

#### score
Points earned when capturing this Pokémon in the Pal Park game.

:type: integer
:minimum: 1
:required:

### default
{{ include:types/default }}

### height
Height, in decimeters.

:type: integer
:minimum: 1
:required:

### weight
Weight, in hectograms.

:type: integer
:minimum: 1
:required:

### experience
Base experience earned when defeating this Pokémon in battle.

:type: integer
:minimum: 1
:required:

### types
A list of [Type](type.md) identifiers.

:type: list
:required:
:minimum values: 1

### egg_groups
A list of [Egg Group](egg_group.md) identifiers.

:type: list

### mega
Is this a Mega Evolution?

:type: boolean
:default: false

### stats
A mapping describing this Pokémon's stats.  Keys are [Stat](stat.md) identifiers.

:type: mapping\[string: mapping\]
:required:

#### base_value
Base stat value

:type: integer
:minimum: 1
:maximum: 255
:required:

#### effort_change
EV earned by opponent when this Pokémon is defeated in battle.

:type: integer
:minimum: 0
:required:

### evolution_conditions
A mapping describing how a Pokémon can evolve into this one.  Keys are
[Evolution Trigger](evolution_trigger.md) identifiers.  Values are one or more
of the conditions listed below.  A Pokémon will evolve under the given trigger 
when *all* of the conditions are true.  See existing data for examples.

:type: mapping\[string: mapping\]

#### trigger_item
Identifier for the [Item](item.md) that will trigger evolution (e.g. evolutionary
stone).
{{ include:types/identifier }}

#### minimum_level
Minimum level

:type: integer
:minimum: 2
:maximum: 100

#### gender
[Gender](gender.md) identifier.
{{ include:types/identifier }}

#### location
Identifier for the [Location](location.md) where evolution can take place.
{{ include:types/identifier }}

#### held_item
Identifier for the [Item](item.md) that must be held.
{{ include:types/identifier }}

#### time_of_day
A list of identifiers for the [Time of Day](time_of_day.md) when evolution can
take place.
{{ include:types/identifier }}

#### known_move
Identifier for the [Move](move.md) the Pokémon must know.
{{ include:types/identifier }}

#### known_move_type
Identifier for the [Type](type.md) of Move a Pokémon must know.
{{ include:types/identifier }}

#### minimum_happiness
Minimum happiness

:type: integer
:minimum: 1
:maximum: 255

#### minimum_beauty
Minimum beauty

:type: integer
:minimum: 1
:maximum: 255

#### minimum_affection
Minimum affection (from Pokémon-Amie or similar)

:type: integer
:minimum: 1
:maximum: 5

#### party_species
Identifier for the Pokémon Species that must be in the party.
{{ include:types/identifier }}

#### party_type
Identifier for the [Type](type.md) of Pokémon that must be in the party.
with.
{{ include:types/identifier }}

#### physical_stats_difference
The difference in physical stats:
- Attack > Defense &rarr; `1`
- Attack < Defense &rarr; `-1`
- Attack = Defense &rarr; `0`

:type: integer
:values: - -1
         - 0
         - 1

#### traded_for_species
Identifier for the Pokémon Species that must be traded for.
{{ include:types/identifier }}

#### overworld_weather
Identifier for the [Weather condition](weather.md) that must be present.
{{ include:types/identifier }}

#### console_inverted
Is the console upside-down?

:type: boolean

### abilities
A mapping describing the Pokémon's Abilities.  Keys are the [Ability](ability.md)
identifier.

:type: mapping\[string: mapping\]

#### position
{{ include:types/position }}
:required:

#### hidden
Is this a [Hidden Ability](https://bulbapedia.bulbagarden.net/wiki/Ability#Hidden_Abilities)?

:type: boolean
:default: false

### wild_held_items
A two-level mapping describing items this Pokémon can be found holding in the
wild.  First-level keys are [Version](version.md) identifiers; second-level keys
are [Item](item.md) identifiers.  Values are the percent chance.  For example:
```yaml
gold:
    lucky-egg: 5
```

:type: mapping\[string: mapping\[string: integer\]\]
:minimum: 1
:maximum: 100

### flavor_text
A mapping describing how this Pokémon is described in the Pokédex.  Keys are
[Version](version.md) identifiers.  Values are flavor text.

:type: mapping\[string: string\]

{{ include:types/flavor_text }}

### evolution_parent
A special string in the format `{species}/{pokemon}` for the Pokémon that
evolves into this one.

:type: string

### forms
An individual form of a Pokémon.

This includes every variant (except shiny differences) of every Pokémon,
regardless of how the games treat them. Even Pokémon with no alternate forms
have one form to represent their lone "normal" form.

Keys are used as identifiers for referencing that specific Form.

:type: mapping
:required:
:minimum values: 1

#### name
{{ include:types/name }}
:required:

#### form_name
Similar to `name`, except only distinguishes this Form from other Forms of the
same Pokémon.  E.g. `name` = `Unown A`, `form_name` = `A`

:type: string
:required:

#### default
{{ include:types/default }}

#### battle_only
Is this Form only available in battle?

:type: boolean
:default: false

#### pokeathlon_stats
This Form's base Pokéathlon stats.  Keys are [Pokéathlon Stat](pokeathlon_stat.md)
identifiers.

:type: mapping\[string: mapping\]

##### base_value
:type: integer
:minimum: 0
:maximum: 5
:required:

##### range
{{ include:types/range }}
:minimum: 0
:maximum: 5
:required:

#### icon
Menu icon.
{{ include:types/asset_path }}

#### sprites
List of battle sprites.
{{ include:types/asset_path }}

:type: list\[string\]

#### art
List of concept art.  Not all Forms will have this.
{{ include:types/asset_path }}

:type: list\[string\]

#### footprint
Pokémon's footprint.
{{ include:types/asset_path }}

#### cry
Pokémon's cry
{{ include:types/asset_path }}
