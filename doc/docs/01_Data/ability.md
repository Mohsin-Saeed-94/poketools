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
:type: string
:required:

## short_description
{{ include:types/markdown }}

## description
{{ include:types/markdown }}
:required:

## flavor_text
{{ include:types/flavor_text }}
