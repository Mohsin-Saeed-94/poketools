---
schema: pokemon_move.json
format: csv
---

# Fields
## species
[Pokemon species](pokemon.md) identifier (e.g. `basculin`).
{{ include:types/identifier }}
:required:

## pokemon
Identifier for the Pokémon in the species above (e.g. `basculin-red-striped`).
{{ include:types/identifier }}
:required:

## version_group
[Version Group](version_group.md) identifier.
{{ include:types/identifier }}
:required:

## move
[Move](move.md) identifier.
{{ include:types/identifier }}
:required:

## learn_method
[Move Learn Method](move_learn_method.md) identifier.
{{ include:types/identifier }}
:required:

## level
Learn level

:type: integer
:minimum: 1
:maximum: 100
:required: Only if `learn_method` is `level-up`

## machine
[Item](item.md) identifier for the Machine.
{{ include:types/identifier }}
:required: Only if `learn_method` is `machine`
