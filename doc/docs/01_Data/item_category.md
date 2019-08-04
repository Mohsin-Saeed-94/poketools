---
schema: item_category.json
format: csv
---

# Fields
## identifier
{{ include:types/identifier }}
:required:

## name
{{ include:types/name }}
:required:

## parent
The parent category identifier.  Omit if this is a root category.

{{ include:types/identifier }}
