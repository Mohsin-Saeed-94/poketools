<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon's affection must be at least this.
 *
 * @ORM\Entity()
 */
class MinimumAffectionEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="5")
     */
    protected $minimumAffection;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('Affection is at least %u', $this->getMinimumAffection());
    }

    /**
     * @return int
     */
    public function getMinimumAffection(): ?int
    {
        return $this->minimumAffection;
    }

    /**
     * @param int $minimumAffection
     *
     * @return self
     */
    public function setMinimumAffection(int $minimumAffection): self
    {
        $this->minimumAffection = $minimumAffection;

        return $this;
    }
}
