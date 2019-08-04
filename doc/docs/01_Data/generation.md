---
schema: generation.json
format: csv
---

# Fields
## id
Generation number

:type: integer
:minimum: 1
:required:

## name
{{ include:types/name }}
:required:

## main_region
Identifier for the primary [region](region.md) in the generation; i.e.
Generation II would be Johto, even through Kanto is playable.

{{ include:types/identifier }}
:required:
