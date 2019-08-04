---
schema: pokemon_color.json
format: csv
---

# Fields
## identifier
{{ include:types/identifier }}
:required:

## name
{{ include:types/name }}
:required:

## css_color
A valid CSS Color string.  See the [CSS 2 Spec](https://www.w3.org/TR/CSS2/syndata.html#color-units)
and the [extended color name list](https://drafts.csswg.org/css-color-3/#svg-color).

:type: string
