---
schema: characteristic.json
format: csv
---

# Fields
## stat
[Stat](stat.md) identifier.
{{ include:types/identifier }}
:required:

## iv_determinator
This is the result of `{highest IV value} % 5`
:type: integer
:minimum: 0
:maximum: 4
:required:

## flavor_text
{{ include:types/flavor_text }}
:required:
