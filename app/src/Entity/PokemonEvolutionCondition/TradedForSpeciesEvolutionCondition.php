<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\PokemonEvolutionCondition;
use App\Entity\PokemonSpeciesInVersionGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon is traded for this species.  It will evolve once the trade is
 * completed.
 *
 * @ORM\Entity()
 */
class TradedForSpeciesEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var PokemonSpeciesInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpeciesInVersionGroup")
     * @Assert\NotBlank()
     */
    protected $tradedForSpecies;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('Traded for []{pokemon:%s}', $this->getTradedForSpecies()->getSlug());
    }

    /**
     * @return PokemonSpeciesInVersionGroup
     */
    public function getTradedForSpecies(): ?PokemonSpeciesInVersionGroup
    {
        return $this->tradedForSpecies;
    }

    /**
     * @param PokemonSpeciesInVersionGroup $tradedForSpecies
     *
     * @return self
     */
    public function setTradedForSpecies(PokemonSpeciesInVersionGroup $tradedForSpecies): self
    {
        $this->tradedForSpecies = $tradedForSpecies;

        return $this;
    }
}
