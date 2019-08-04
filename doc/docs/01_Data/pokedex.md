---
schema: pokedex.json
format: yaml
---

# Filename
Pokédex identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## description
{{ include:types/description }}
:required:

## version_groups
A list of [Version Group](version_group.md) identifiers that contain this
Pokédex.

:type: list
:required:
