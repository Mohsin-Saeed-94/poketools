---
schema: growth_rate.json
format: yaml
---

# Filename
Growth Rate identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## formula
The user-facing representation of the growth rate formula as a
[Presentation MathML](https://en.wikipedia.org/wiki/MathML) document, including
surrounding `<math>` tags.

:type: string
:required:

## expression
The computed representation of the growth rate formula, as a
[Symfony Expression](https://symfony.com/doc/current/components/expression_language/syntax.html).
In addition to the standard Expression functions, all
[PHP Math functions](https://www.php.net/manual/en/ref.math.php) are available.

:type: string
:required:
