<?php

namespace App\Validator;

use App\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsExpressionValidator extends ConstraintValidator
{

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * IsExpressionValidator constructor.
     *
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * {@inheritdoc}
     * @param IsExpression $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        // Attempt to compile the expression
        try {
            $this->expressionLanguage->compile($value);
        } catch (SyntaxError $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ message }}', $e->getMessage())
                ->addViolation();
        }
    }
}
