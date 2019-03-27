<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use App\Entity\Type;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokémon of this type must be present in the party.
 *
 * @ORM\Entity()
 */
class PartyTypeEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @Assert\NotBlank()
     */
    protected $partyType;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('[]{type:%s}-type Pokèmon is in the current party', $this->getPartyType()->getSlug());
    }

    /**
     * @return Type
     */
    public function getPartyType(): ?Type
    {
        return $this->partyType;
    }

    /**
     * @param Type $partyType
     *
     * @return self
     */
    public function setPartyType(Type $partyType): self
    {
        $this->partyType = $partyType;

        return $this;
    }
}
