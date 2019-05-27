<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\PokemonEvolutionCondition;
use App\Entity\PokemonSpeciesInVersionGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A species must be present in the party.
 *
 * @ORM\Entity()
 */
class PartySpeciesEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var PokemonSpeciesInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpeciesInVersionGroup")
     * @Assert\NotBlank()
     */
    protected $partySpecies;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('[]{pokemon:%s} is in the current party', $this->getPartySpecies()->getSlug());
    }

    /**
     * @return PokemonSpeciesInVersionGroup
     */
    public function getPartySpecies(): ?PokemonSpeciesInVersionGroup
    {
        return $this->partySpecies;
    }

    /**
     * @param PokemonSpeciesInVersionGroup $partySpecies
     *
     * @return self
     */
    public function setPartySpecies(PokemonSpeciesInVersionGroup $partySpecies): self
    {
        $this->partySpecies = $partySpecies;

        return $this;
    }
}
