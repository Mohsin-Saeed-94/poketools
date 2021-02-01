<?php


namespace App\Tests\dataschema\Filter;

use App\Validator\CssColor as CssColorConstraint;
use Opis\JsonSchema\IFilter;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validate data is a CSS color string
 */
class CssColor implements IFilter
{
    private ValidatorInterface $validator;

    private array $constraints;

    public function __construct()
    {
        // Delegate to the entity validator
        $this->validator = Validation::createValidator();
        $this->constraints = [
            new CssColorConstraint(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function validate($data, array $args): bool
    {
        $violations = $this->validator->validate($data, $this->constraints);

        return count($violations) === 0;
    }

}
