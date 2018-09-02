<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsExpression extends Constraint
{

    public $message = 'The expression "{{ value }}" is not valid:'."\n{{ message }}";
}
