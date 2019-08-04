---
schema: weather.json
format: csv
---

# Fields
## identifier
{{ include:types/identifier }}
:required:

## name
{{ include:types/name }}
:required:

## battle_only
Is this effect battle only?

:type: boolean
:values: - 0
         - 1
:required:
