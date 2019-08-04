---
schema: encounter.json
format: csv
---

# Fields
## id
{{ include:types/id }}

## version
[Version](version.md) identifier.
{{ include:types/identifier }}
:required:

## location
[Location](location.md) identifier.
{{ include:types/identifier }}
:required:

## area
Identifier for the area in the location above.
{{ include:types/identifier }}
:required:

## method
[Encounter method](encounter_method.md) identifier.
{{ include:types/identifier }}
:required:

## species
[Pokemon species](pokemon.md) identifier (e.g. `basculin`).
{{ include:types/identifier }}
:required:

## pokemon
Identifier for the Pokémon in the species above (e.g. `basculin-red-striped`).
{{ include:types/identifier }}
:required:

## level
Level range
{{ include:types/range }}
:minimum: `1`
:maximum: `100`
:required:

## chance
Encounter chance percentage.

If this is a fateful encounter (e.g. from an event or in-game story point),
leave this empty.

:type: integer|null
:minimum: 1
:maximum: 100

## conditions
A list of identifiers for [conditions](encounter_condition.md) that must be met
for this encounter to occur.  Format these as `condition/condition-state`.  If
multiple conditions must occur simultaneously, separate them with a single comma.
For example:
- `time/time-day`
- `time/time-morning,radio/radio-off`

:type: string
:pattern: `^([a-z0-9\-]+/[a-z0-9\-]+,?)+$` ([Test](https://regex101.com/r/iJXwNb/2))

## note
A special note about this encounter.  If this is a fateful encounter (i.e.
`chance` is blank), describe the circumstances here.

{{ include:types/markdown }}
