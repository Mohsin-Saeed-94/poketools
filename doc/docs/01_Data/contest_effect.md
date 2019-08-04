---
schema: contest_effect.json
format: csv
---

# Fields
## id
{{ include:types/id }}
:required:

## appeal
Appeal points

:type: integer
:minimum: 0
:required:

## jam
Jam points

:type: integer
:minimum: 0
:required:

## flavor_text
{{ include:types/flavor_text }}
:required:

## description
{{ include:types/description }}
:required:
