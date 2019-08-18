---
schema: shop_item.json
format: csv
---

# Fields
## version_group
Version Group identifier.
{{ include:types/identifier }}
:required:

## location
Location identifier.
{{ include:types/identifier }}
:required:

## area
Area identifier path.

This is written like a filesystem path, e.g. `department-store/2f`.

:type: string
:pattern: `^(?:[a-z0-9\-]+/?)+$` ([Test](https://regex101.com/r/hiVwli/1))
:required:

## shop
Shop identifier
{{ include:types/identifier }}
:required:

## item
Item identifier
{{ include:types/identifier }}
:required:

## buy
Purchase price

:type: integer
:minimum: 1
:required:
