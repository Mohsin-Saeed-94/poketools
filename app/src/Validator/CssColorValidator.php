<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CssColorValidator extends ConstraintValidator
{

    /**
     * List of "basic" css colors.
     *
     * Taken from https://www.w3.org/TR/CSS2/syndata.html#color-units and
     * https://drafts.csswg.org/css-color-3/#svg-color
     */
    protected const CSS_COLOR_NAMES = [
      'aqua',
      'black',
      'blue',
      'fuchsia',
      'gray',
      'green',
      'lime',
      'maroon',
      'navy',
      'olive',
      'orange',
      'purple',
      'red',
      'silver',
      'teal',
      'white',
      'yellow',
      'aliceblue',
      'antiquewhite',
      'aqua',
      'aquamarine',
      'azure',
      'beige',
      'bisque',
      'black',
      'blanchedalmond',
      'blue',
      'blueviolet',
      'brown',
      'burlywood',
      'cadetblue',
      'chartreuse',
      'chocolate',
      'coral',
      'cornflowerblue',
      'cornsilk',
      'crimson',
      'cyan',
      'darkblue',
      'darkcyan',
      'darkgoldenrod',
      'darkgray',
      'darkgreen',
      'darkgrey',
      'darkkhaki',
      'darkmagenta',
      'darkolivegreen',
      'darkorange',
      'darkorchid',
      'darkred',
      'darksalmon',
      'darkseagreen',
      'darkslateblue',
      'darkslategray',
      'darkslategrey',
      'darkturquoise',
      'darkviolet',
      'deeppink',
      'deepskyblue',
      'dimgray',
      'dimgrey',
      'dodgerblue',
      'firebrick',
      'floralwhite',
      'forestgreen',
      'fuchsia',
      'gainsboro',
      'ghostwhite',
      'gold',
      'goldenrod',
      'gray',
      'green',
      'greenyellow',
      'grey',
      'honeydew',
      'hotpink',
      'indianred',
      'indigo',
      'ivory',
      'khaki',
      'lavender',
      'lavenderblush',
      'lawngreen',
      'lemonchiffon',
      'lightblue',
      'lightcoral',
      'lightcyan',
      'lightgoldenrodyellow',
      'lightgray',
      'lightgreen',
      'lightgrey',
      'lightpink',
      'lightsalmon',
      'lightseagreen',
      'lightskyblue',
      'lightslategray',
      'lightslategrey',
      'lightsteelblue',
      'lightyellow',
      'lime',
      'limegreen',
      'linen',
      'magenta',
      'maroon',
      'mediumaquamarine',
      'mediumblue',
      'mediumorchid',
      'mediumpurple',
      'mediumseagreen',
      'mediumslateblue',
      'mediumspringgreen',
      'mediumturquoise',
      'mediumvioletred',
      'midnightblue',
      'mintcream',
      'mistyrose',
      'moccasin',
      'navajowhite',
      'navy',
      'oldlace',
      'olive',
      'olivedrab',
      'orange',
      'orangered',
      'orchid',
      'palegoldenrod',
      'palegreen',
      'paleturquoise',
      'palevioletred',
      'papayawhip',
      'peachpuff',
      'peru',
      'pink',
      'plum',
      'powderblue',
      'purple',
      'red',
      'rosybrown',
      'royalblue',
      'saddlebrown',
      'salmon',
      'sandybrown',
      'seagreen',
      'seashell',
      'sienna',
      'silver',
      'skyblue',
      'slateblue',
      'slategray',
      'slategrey',
      'snow',
      'springgreen',
      'steelblue',
      'tan',
      'teal',
      'thistle',
      'tomato',
      'turquoise',
      'violet',
      'wheat',
      'white',
      'whitesmoke',
      'yellow',
      'yellowgreen',
    ];

    /**
     * A hex digit.
     */
    protected const PATTERN_PART_HEX_DIGIT = '[A-Fa-f0-9]';

    /**
     * Matches hex-style color declarations in both short and long form, with
     * and without alpha.
     */
    protected const PATTERN_COLOR_HEX = '`^\#(?:(?:(?:'.self::PATTERN_PART_HEX_DIGIT.'{2}){3,4})|(?:'.self::PATTERN_PART_HEX_DIGIT.'{3,4}))$`';

    /**
     * A valid digit in a functional-style color declaration.
     */
    protected const PATTERN_PART_FUNCTIONAL_DIGIT = '[\d+-.e]';

    /**
     * The separator between parameters in a functional-style color declaration.
     */
    protected const PATTERN_PART_FUNCTIONAL_SEPARATOR = ',? *';

    /**
     * The separator between color parameters and the alpha parameter in a
     * functional-style color declaration.
     */
    protected const PATTERN_PART_FUNCTIONAL_ALPHA_SEPARATOR = '(?:(?:'.self::PATTERN_PART_FUNCTIONAL_SEPARATOR.')|(?: ?/ ?))';

    /**
     * Matches rgb()/rgba() functional-style color declarations.
     *
     * This requires extra checking to ensure parameter types are not mixed and
     * are within valid ranges.  It defines the groups "r", "g", and "b", as
     * well as "a" if the alpha value is specified.  The function used is in the
     * group "function".
     */
    protected const PATTERN_COLOR_RGB = '`^(?P<function>rgba?)\((?P<r>'.self::PATTERN_PART_FUNCTIONAL_DIGIT.'+%?)'.self::PATTERN_PART_FUNCTIONAL_SEPARATOR.'(?P<g>'.self::PATTERN_PART_FUNCTIONAL_DIGIT.'+%?)'.self::PATTERN_PART_FUNCTIONAL_SEPARATOR.'(?P<b>'.self::PATTERN_PART_FUNCTIONAL_DIGIT.'+%?)(?:'.self::PATTERN_PART_FUNCTIONAL_ALPHA_SEPARATOR.'(?P<a>'.self::PATTERN_PART_FUNCTIONAL_DIGIT.'+%?))?\)$`';

    /**
     * Matches hsl()/hsla() functional-style color declarations.
     *
     * This requires extra checking to ensure parameter types are not mixed and
     * are within valid ranges.  It defines the groups "h", "s", and "l", as
     * well as "a" if the alpha value is specified.  The function used is in the
     * group "function".
     */
    protected const PATTERN_COLOR_HSL = '`^(?P<function>hsla?)\((?P<h>[\d+-.]+(?:(?:deg)|(?:rad)|(?:grad)|(?:turn))?)'.self::PATTERN_PART_FUNCTIONAL_SEPARATOR.'(?P<s>\d+%)'.self::PATTERN_PART_FUNCTIONAL_SEPARATOR.'(?P<l>\d+%)(?:'.self::PATTERN_PART_FUNCTIONAL_ALPHA_SEPARATOR.'(?P<a>[\d+-.]+%?))?\)$`';

    /**
     * {@inheritdoc}
     *
     * @param mixed    $value
     * @param CssColor $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        if (!is_string($value)) {
            $this->context->addViolation('This value must be a string.');

            return;
        }

        $messages = [];
        if ($constraint->hex && $this->isHexColor($value, $messages)) {
            // Valid
            return;
        }
        if ($constraint->colorNames && $this->isColorName($value, $messages)) {
            // Valid
            return;
        }
        if ($constraint->rgb && $this->isRgb($value, $messages)) {
            // Valid
            return;
        }
        if ($constraint->hsl && $this->isHsl($value, $messages)) {
            // Valid
            return;
        }
        if ($constraint->transparent && $this->isTransparent($value, $messages)) {
            // Valid
            return;
        }
        if ($constraint->currentColor && $this->isCurrentColor($value, $messages)) {
            // Valid
            return;
        }

        // At this point, it's been determined the value is not valid.
        if (empty($messages)) {
            // Fallback invalid message
            $message = 'This is not a valid CSS color.';
        } else {
            $message = 'This is not a valid CSS color: '.implode(', ', $messages);
        }
        $this->context->addViolation($message);
    }

    /**
     * Is this a hex-style color declaration?
     *
     * @param string $color
     * @param array  $messages
     *   A list of messages detailing why the value did not fit the criteria.
     *
     * @return bool
     */
    protected function isHexColor(string $color, array &$messages = null): bool
    {
        if (!isset($messages)) {
            $messages = [];
        }

        if (preg_match(self::PATTERN_COLOR_HEX, $color) !== false) {
            return true;
        } else {
            $messages[] = 'This is not a valid hex color';

            return false;
        }
    }

    /**
     * Is the name a valid CSS color name?
     *
     * @param string $color
     * @param array  $messages
     *   A list of messages detailing why the value did not fit the criteria.
     *
     * @return bool
     */
    protected function isColorName(string $color, array &$messages = null): bool
    {
        if (!isset($messages)) {
            $messages = [];
        }

        if (in_array($color, self::CSS_COLOR_NAMES)) {
            return true;
        } else {
            $messages[] = 'This is not a defined CSS color name';

            return false;
        };
    }

    /**
     * Is this a valid rgb()/rgba() declaration?
     *
     * @param string $color
     * @param array  $messages
     *   A list of messages detailing why the value did not fit the criteria.
     *
     * @return bool
     */
    protected function isRgb(string $color, array &$messages = null): bool
    {
        if (!isset($messages)) {
            $messages = [];
        }

        // Is the rgb()/rgba() function used?
        $mightBeRgb = preg_match(self::PATTERN_COLOR_RGB, $color, $matches);
        if ($mightBeRgb === false) {
            $messages[] = 'The rgb()/rgba() function is not used';

            return false;
        }

        $colorKeys = ['r', 'g', 'b'];

        // If rgba() was used, ensure alpha is present
        if ($matches['function'] == 'rgba' && !isset($matches['a'])) {
            $messages[] = 'rgba() is used but no alpha value is set';

            return false;
        }

        // Don't use floats in rgb() without alpha
        if (!isset($matches['a'])) {
            foreach ($colorKeys as $key) {
                $value = $matches[$key];
                if ($this->isFloat($value)) {
                    $messages[] = 'Float values cannot be used for colors in rgb() without an alpha value set';

                    return false;
                }
            }
        }

        // Don't mix ints and percents
        $usesPercent = $this->isPercentage($matches[$colorKeys[0]]);
        foreach (array_slice($colorKeys, 1) as $key) {
            $value = $matches[$key];
            if ($usesPercent !== $this->isPercentage($value)) {
                $messages[] = 'Integers and percentages cannot be mixed in rgb()/rgba()';

                return false;
            }
        }

        // Ensure values are in range
        foreach ($colorKeys as $key) {
            $value = $matches[$key];
            if ($this->isPercentage($value)) {
                $value = (int)$value;
                if ($value < 0 || $value > 100) {
                    $messages[] = 'The value for '.$key.' is out of range';

                    return false;
                }
            } else {
                $value = (float)$value;
                if ($value < 0 || $value > 255) {
                    $messages[] = 'The value for '.$key.' is out of range';

                    return false;
                }
            }
        }
        if (isset($matches['a'])) {
            if ($this->isPercentage($matches['a'])) {
                $value = (int)$matches['a'];
                if ($value < 0 || $value > 100) {
                    $messages[] = 'The value for alpha is out of range';

                    return false;
                }
            } else {
                $value = (float)$matches['a'];
                if ($value < 0 || $value > 1) {
                    $messages[] = 'The value for alpha is out of range';

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Is this a float?
     *
     * @param string $value
     *
     * @return bool
     */
    private function isFloat(string $value): bool
    {
        return strpos($value, '.') !== false;
    }

    /**
     * Is this value a percentage?
     *
     * @param string $value
     *
     * @return bool
     */
    private function isPercentage(string $value): bool
    {
        return strpos($value, '%') !== false;
    }

    /**
     * Is this a valid hsl()/hsla() declaration?
     *
     * @param string $color
     * @param array  $messages
     *   A list of messages detailing why the value did not fit the criteria.
     *
     * @return bool
     */
    protected function isHsl(string $color, array &$messages = null): bool
    {
        if (!isset($messages)) {
            $messages = [];
        }

        $mightBeHsl = preg_match(self::PATTERN_COLOR_HSL, $color, $matches);
        if ($mightBeHsl === false) {
            $messages[] = 'The rgb()/rgba() function is not used';

            return false;
        }

        // If hsla() was used, ensure alpha is present
        if ($matches['function'] == 'hsla' && !isset($matches['a'])) {
            $messages[] = 'rgba() is used but no alpha value is set';

            return false;
        }

        // Ensure values are in range.  The hue will wrap by spec, so there is
        // no range defined for hue.
        foreach (['s', 'l'] as $key) {
            $value = (int)$matches[$key];
            if ($value < 0 || $value > 100) {
                $messages[] = 'The value for '.$key.' is out of range';

                return false;
            }
        }
        if (isset($matches['a'])) {
            if ($this->isPercentage($matches['a'])) {
                $value = (int)$matches['a'];
                if ($value < 0 || $value > 100) {
                    $messages[] = 'The value for alpha is out of range';

                    return false;
                }
            } else {
                $value = (float)$matches['a'];
                if ($value < 0 || $value > 1) {
                    $messages[] = 'The value for alpha is out of range';

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Does the name specify transparent?
     *
     * @param string $color
     * @param array  $messages
     *   A list of messages detailing why the value did not fit the criteria.
     *
     * @return bool
     */
    protected function isTransparent(string $color, array &$messages = null): bool
    {
        if (!isset($messages)) {
            $messages = [];
        }

        if ($color === 'transparent') {
            return true;
        } else {
            $messages[] = 'Value is not "transparent"';

            return false;
        };
    }

    /**
     * Does the name specify current color?
     *
     * @param string $color
     * @param array  $messages
     *   A list of messages detailing why the value did not fit the criteria.
     *
     * @return bool
     */
    protected function isCurrentColor(string $color, array &$messages = null): bool
    {
        if (!isset($messages)) {
            $messages = [];
        }

        if ($color === 'currentColor') {
            return true;
        } else {
            $messages[] = 'Value is not "currentColor"';

            return false;
        }
    }
}
