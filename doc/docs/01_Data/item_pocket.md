---
schema: item_pocket.json
format: yaml
---

# Filename
Item Pocket identifier

{{ include:types/identifier }}

# Top-level keys
Version group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## position
{{ include:types/position }}

## icon
{{ include:types/asset_path }}
:required:
