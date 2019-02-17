<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The number of a species in a particular Pokédex
 *
 * @ORM\Entity(repositoryClass="PokemonSpeciesPokedexNumberRepository")
 */
class PokemonSpeciesPokedexNumber implements EntityHasDefaultInterface
{

    use EntityHasDefaultTrait;

    /**
     * @var PokemonSpeciesInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpeciesInVersionGroup", inversedBy="numbers")
     * @ORM\Id()
     */
    protected $species;

    /**
     * @var Pokedex
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokedex")
     * @ORM\Id()
     */
    protected $pokedex;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $number;

    /**
     * @return PokemonSpeciesInVersionGroup
     */
    public function getSpecies(): ?PokemonSpeciesInVersionGroup
    {
        return $this->species;
    }

    /**
     * @param PokemonSpeciesInVersionGroup $species
     *
     * @return self
     */
    public function setSpecies(PokemonSpeciesInVersionGroup $species): self
    {
        $this->species = $species;

        return $this;
    }

    /**
     * @return Pokedex
     */
    public function getPokedex(): ?Pokedex
    {
        return $this->pokedex;
    }

    /**
     * @param Pokedex $pokedex
     *
     * @return self
     */
    public function setPokedex(Pokedex $pokedex): self
    {
        $this->pokedex = $pokedex;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return self
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }
}
