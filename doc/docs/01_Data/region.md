---
schema: region.json
format: yaml
---

# Filename
Region identifier

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
:required:

## maps
A mapping with information about the map graphics for this region.  Keys are
used as an identifier to refer to this map graphic.

:type: mapping\[string: mapping\]

### name
{{ include:types/name }}
:required:

## url
{{ include:types/asset_path }}
:required:
