<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CssColor extends Constraint
{

    /**
     * Allow using predefined CSS color names, e.g. "Red".
     *
     * @var bool
     */
    public $colorNames = true;

    /**
     * Allow using hex notation (e.g. #663399)
     *
     * @var bool
     */
    public $hex = true;

    /**
     * Allow using the rgb()/rgba() functions.
     *
     * @var bool
     */
    public $rgb = true;

    /**
     * Allow uding the hsl()/hsla() functions.
     *
     * @var bool
     */
    public $hsl = true;

    /**
     * Allow using the "transparent" keyword as a color.
     *
     * Defaults to true.
     *
     * @var bool
     */
    public $transparent = true;

    /**
     * Allow using the "currentColor" keyword as a color.
     *
     * Defaults to true.
     *
     * @var bool
     */
    public $currentColor = true;
}
