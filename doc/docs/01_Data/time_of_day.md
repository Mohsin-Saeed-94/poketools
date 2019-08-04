---
schema: time_of_day.json
format: csv
---

# Fields
## generation
[Generation](generation.md) ID.
{{ include:types/id }}
:required:

## identifier
{{ include:types/identifier }}
:required:

## name
{{ include:types/name }}
:required:

## starts
Start time (24-hour format, UTC, inclusive), e.g. `04:00:00`

:type: string
:required:

## ends
End time (24-hour format, UTC, inclusive), e.g. `09:59:59`

:type: string
:required:
