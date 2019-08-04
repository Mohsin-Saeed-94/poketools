---
schema: type.json
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
[Damage Class](move_damage_class.md) identifier for versions where damage class
comes from the type.  Types introduced after the switch will have this blank.
{{ include:types/identifier }}

## hidden
Is this type normally shown?  If this is set the type will generally be excluded
from displayed type lists unless it is specifically involved in the matchup.

:type: boolean
:values: - 0
         - 1
:default: 0
:required:
