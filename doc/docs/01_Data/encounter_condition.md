---
schema: encounter_condition.json
format: yaml
---

# Filename
Condition identifier

{{ include:types/identifier }}

# Fields
## name
{{ include: types/name }}
:required:

## position
{{ include:types/position }}

## states
A map of possible states for this condition.  The key for each mapping is used
as the identifier for the state in places like the [encounter](encounter.md)
list.

### name
{{ include: types/name }}
:required:

### default
{{ include:types/default }}
