<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokémon's internal beauty value must be at least this.
 *
 * @ORM\Entity()
 */
class MinimumBeautyEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="1", max="255")
     */
    protected $minimumBeauty;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('Beauty is at least %u', $this->getMinimumBeauty());
    }

    /**
     * @return int
     */
    public function getMinimumBeauty(): ?int
    {
        return $this->minimumBeauty;
    }

    /**
     * @param int $minimumBeauty
     *
     * @return self
     */
    public function setMinimumBeauty(int $minimumBeauty): self
    {
        $this->minimumBeauty = $minimumBeauty;

        return $this;
    }
}
