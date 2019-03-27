<?php

namespace App\Entity;

use App\Validator\IsExpression;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Growth rate of a Pokémon, i.e. the EXP to level function.
 *
 * @ORM\Entity(repositoryClass="App\Repository\GrowthRateRepository")
 */
class GrowthRate extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * MathML representation of the growth rate formula
     *
     * This will include `<math>` tags.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    protected $formula;

    /**
     * Usable expression for the formula
     *
     * This uses the Symfony ExpressionLanguage.
     *
     * @see https://symfony.com/doc/current/components/expression_language.html
     * @see https://symfony.com/doc/current/components/expression_language/syntax.html
     *
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @IsExpression()
     */
    protected $expression;

    /**
     * @return string
     */
    public function getFormula(): ?string
    {
        return $this->formula;
    }

    /**
     * @param string $formula
     *
     * @return self
     */
    public function setFormula(string $formula): self
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }

    /**
     * @param string $expression
     *
     * @return self
     */
    public function setExpression(string $expression): self
    {
        $this->expression = $expression;

        return $this;
    }
}
