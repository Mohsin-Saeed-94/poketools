---
schema: stat.json
format: csv
---

# Fields
## identifier
{{ include:types/identifier }}
:required:

## name
{{ include:types/name }}
:required:

## damage_class
If this stat plays a part in damage calculations, this is the identifier for the
[Damage Class](move_damage_class.md) it should be involved with.
{{ include:types/identifier }}

## battle_only
Is stat battle only?

:type: boolean
:values: - 0
         - 1
:required:
