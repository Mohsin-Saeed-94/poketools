---
schema: version_group.json
format: yaml
---

# Filename
Version Group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## position
{{ include:types/position }}
:required:

## generation
[Generation](generation.md) ID.
{{ include:types/id }}
:required:

## features
A list of [Feature](feature.md) identifiers.  Features are functionality that
sets this version group apart from others.  This will enable/disable certain
site functionality on entity pages.

:type: list
:required:
