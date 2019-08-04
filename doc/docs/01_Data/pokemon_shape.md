---
schema: pokemon_shape.json
format: yaml
---

# Filename
Pokémon Shape identifier

{{ include:types/identifier }}

# Top-level keys
Version group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## taxonomy_name
A taxonomy name for this shape, roughly corresponding to a family name in
zoological taxonomy.

:type: string
:required:

## description
{{ include:types/description }}

## icon
{{ include:types/asset_path }}
:required:
