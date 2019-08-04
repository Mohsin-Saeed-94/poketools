---
schema: move_ailment.json
format: csv
---

# Fields
## identifier
{{ include:types/identifier }}
:required:

## name
{{ include:types/name }}
:required:

## volatile
A volatile status condition will wear off when a Pokemon is switched out or when
a battle ends.

:type: boolean
:values: * 0
         * 1
:default: 0
:required:
