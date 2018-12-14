<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon must be at least this level.
 *
 * @ORM\Entity()
 */
class MinimumLevelEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="1", max="100")
     *
     * @Groups("read")
     */
    protected $minimumLevel;

    /**
     * @return int
     */
    public function getMinimumLevel(): ?int
    {
        return $this->minimumLevel;
    }

    /**
     * @param int $minimumLevel
     *
     * @return self
     */
    public function setMinimumLevel(int $minimumLevel): self
    {
        $this->minimumLevel = $minimumLevel;

        return $this;
    }
}
