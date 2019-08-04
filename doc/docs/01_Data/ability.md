---
schema: ability.json
format: yaml
---

# Filename
Ability identifier

{{ include:types/identifier }}

# Top-level keys
Version group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include: types/name }}
:required:

## short_description
{{ include:types/short_description }}

## description
{{ include:types/description }}
:required:

## flavor_text
{{ include:types/flavor_text }}
