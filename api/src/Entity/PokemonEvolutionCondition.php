<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The conditions under which a Pokémon will evolve into this Pokémon.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonEvolutionConditionRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
class PokemonEvolutionCondition extends AbstractDexEntity
{

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon")
     * @Assert\NotBlank()
     */
    protected $pokemon;

    /**
     * @var EvolutionTrigger
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EvolutionTrigger")
     * @Assert\NotBlank()
     */
    protected $evolutionTrigger;

    /**
     * PokemonEvolutionCondition constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return Pokemon
     */
    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return self
     */
    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * @return EvolutionTrigger
     */
    public function getEvolutionTrigger(): EvolutionTrigger
    {
        return $this->evolutionTrigger;
    }

    /**
     * @param EvolutionTrigger $evolutionTrigger
     *
     * @return self
     */
    public function setEvolutionTrigger(EvolutionTrigger $evolutionTrigger): self
    {
        $this->evolutionTrigger = $evolutionTrigger;

        return $this;
    }
}
