---
schema: nature.json
format: yaml
---

# Filename
Nature identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## stat_increased
Identifier for the [Stat](stat.md) this Nature increases.
{{ include:types/identifier }}
:required:

## stat_decreased
Identifier for the [Stat](stat.md) this Nature decreases.
{{ include:types/identifier }}
:required:

## flavor_likes
Identifier for the [Berry flavor](berry_flavor.md) Pokémon with this Nature will
like.
{{ include:types/identifier }}
:required:

## flavor_hates
Identifier for the [Berry flavor](berry_flavor.md) Pokémon with this Nature will
hate.
{{ include:types/identifier }}
:required:

## battle_style_preferences
How a Pokemon with this nature will behave in the Battle Tent/Battle Palace.
Keys are the [Battle Style](battle_style.md) identifier.

:type: mapping\[string: mapping\]
:required:

### low_hp_chance
:type: integer
:minimum: 0
:maximum: 100
:required:

### high_hp_chance
:type: integer
:minimum: 0
:maximum: 100
:required:

## pokeathlon_stat_changes
How this nature affects a Pokémon's Pokéathlon stats.  Keys are [Pokéathlon Stat](pokeathlon_stat.md)
identifiers, values are the range of changes.

:type: mapping\[string: int|string\]
:required:
