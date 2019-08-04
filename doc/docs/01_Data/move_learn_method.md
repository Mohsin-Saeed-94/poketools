---
schema: move_learn_method.json
format: yaml
---

# Filename
Move effect id

{{ include:types/id }}

# Fields
## name
{{ include:types/name }}
:required:

## sort
{{ include:types/position }}

## description
{{ include:types/description }}
:required:

## version_groups
List of identifiers for [Version groups](version_group.md) where this learn
method is used.

:type: list
