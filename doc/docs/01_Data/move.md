---
schema: move.json
format: yaml
---

# Filename
Move identifier

{{ include:types/identifier }}

# Top-level keys
Version group identifier

{{ include:types/identifier }}

# Fields
## name
{{ include:types/name }}
:required:

## crit_rate_bonus
Critical-hit rate bonus

:type: integer
:minimum: 1

## drain
Drain amount, used by draining moves like *Leach life*.

:type: integer
:minimum: 1

## flinch_chance
The percent chance this move will cause the target to flinch.

:type: integer
:minimum: 1
:maximum: 100

## ailment
[Move Ailment](move_ailment.md) identifier.
{{ include:types/identifier }}

## ailment_chance
The percent chance of inflicting the above ailment on the target.

:type: integer
:minimum: 1
:maximum: 100

## recoil
Recoil damage, as a percentage of damage dealt.

:type: integer
:minimum: 1
:maximum: 100

## healing
The amount the target will be healed, as a percentage of the target's maximum
HP.  Note this can be a negative number for moves that will inflict damage based
on the target's maximum HP, not the user's power or some other calculation.

:type: integer
:minimum: -100
:maximum: 100

## flags
A list of [Move Flag](move_flag.md) identifiers.  Flags are special attributes
that affect how the game treats this move.

:type: list\[string\]

## categories
A list of [Move category](move_category.md) identifiers.

:type: list\[string\]

## hits
How many times this move will hit in a single turn.

{{ include:types/range }}
:default: 1

## turns
How many turns this move will last, including both damage-dealing and
non-damage-dealing turns (e.g. charge)

{{ include:types/range }}
:default: 1

## stat_changes
Stat changes this move will cause on the target, both increases and decreases.
The key is the [Stat](stat.md) identifier, the value is the change.

:type: mapping\[string: integer\]

## stat_change_chance
The chance of the stat change above affecting the target.

:type: integer
:minimum: 1
:maximum: 100

## type
[Type](type.md) identifier.
{{ include:types/identifier }}
:required:

## power
Move power

:type: integer
:minimum: 1

## pp
Move PP

:type: integer
:minimum: 1

## accuracy
If this move doesn't consider accuracy (e.g. moves that affect the current team),
omit this field.

:type: integer
:minimum: 1
:maximum: 100

## priority
Move [priority](https://bulbapedia.bulbagarden.net/wiki/Priority)

:type: integer
:default: 0

## target
[Target](move_target.md) identifier.
{{ include:types/identifier }}
:required:

## damage_class
Move [Damage Class](move_damage_class.md) identifier, where applicable to the
generation.
{{ include:types/identifier }}

## effect
[Move Effect](move_effect.md) ID.
{{ include:types/id }}
:required:

## effect_chance
Chance of causing some secondary effect on the target.  Often used as a part of
the effect description, as moves with the same effect may have a different
chance of causing the same secondary effect.

:type: integer
:minimum: 1
:maximum: 100

## contest_type
[Contest type](contest_type.md) identifier.
{{ include:types/identifier }}

## contest_effect
[Contest Effect](contest_effect.md) ID.
{{ include:types/identifier }}

## super_contest_effect
[Super Contest Effect](super_contest_effect.md) ID.
{{ include:types/identifier }}

## contest_use_before
Identifiers for moves to use after this one as part of a combo in Contests.

:type: list

## contest_use_after
Identifiers for moves to use before this one as part of a combo in Contests.

:type: list

## super_contest_use_before
Identifiers for moves to use after this one as part of a combo in Super Contests.

:type: list

## super_contest_use_after
Identifiers for moves to use before this one as part of a combo in Super Contests.

:type: list
