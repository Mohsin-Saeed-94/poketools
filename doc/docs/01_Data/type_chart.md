---
schema: type_chart.json
format: yaml
---

# Filename
Type Chart ID

{{ include:types/id }}

# Fields
## version_groups
A list of [Version Group](version_group.md) identifiers.
{{ include:types/identifier }}
:required:

## efficacy
A two-level mapping describing the efficacy of a type matchup.  The first-level
key is the attacking [Type](type.md) identifier, the second-level key is the
defending [Type](type.md) identifier.  The value is the efficacy as a percentage.
For example:
```yaml
fighting:
    normal: 200
```

:type: mapping\[string: mapping\[string: int\]\]
:values: - 0
         - 50
         - 100
         - 200
:required:
