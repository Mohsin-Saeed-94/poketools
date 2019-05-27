<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon's internal happiness value must be at least this.
 *
 * @ORM\Entity()
 */
class MinimumHappinessEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="255")
     */
    protected $minimumHappiness;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('Happiness is at least %u', $this->getMinimumHappiness());
    }

    /**
     * @return int
     */
    public function getMinimumHappiness(): ?int
    {
        return $this->minimumHappiness;
    }

    /**
     * @param int $minimumHappiness
     *
     * @return self
     */
    public function setMinimumHappiness(int $minimumHappiness): self
    {
        $this->minimumHappiness = $minimumHappiness;

        return $this;
    }
}
