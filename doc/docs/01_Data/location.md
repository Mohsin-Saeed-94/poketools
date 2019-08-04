---
schema: location.json
format: yaml
---

# Filename
Location identifier

{{ include:types/identifier }}

# Top-level keys
Version group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## region
[Region](region.md) identifier.
{{ include:types/identifier }}
:required:

## description
If special conditions are required for access, describe them here.

{{ include:types/description }}

## areas
A mapping describing the areas in this location.  The keys are used as the area
identifier when referring to that area from other entities.

Every location must have at least one area.  By convention, if a location has
only one area that area's identifier is `whole-area` and name is `Whole area`.

:type: mapping
:minimum values: 1
:required:

### name
{{ include:types/name }}
:required:

### default
Every location must have exactly one default area.
{{ include:types/default }}

## super
A parent location.  A parent location is generally the location that contains
this one entirely.  For example, the Kanto Safari Zone would list `fuchsia-city`
as it's super.

{{ include:types/identifier }}

## map
Map data.  In general, every location should include map data if it has an
explicit location on the in-game map.  If no map data is defined but the parent
location has map data, that will be used instead.  If the location does not have
a place on the in-game map (e.g. Distortion World), omit this information.

:type: mapping

### map
Identifier for the map to draw this overlay on. Maps are defined in
[Region](region.md) data.

{{ include:types/identifier }}
:required:

### z
Items with a higher z-index will be drawn on top of items with a lower z-index.
This could be helpful for a location in the middle of a route, for example.

:type: integer
:default: 0

### overlay
The SVG data that makes up the overlay.  Do not enclose this in `<svg>` tags, as
it may be embedded with other overlays.

:type: string
:required:
