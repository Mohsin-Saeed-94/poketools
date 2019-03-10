<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A Pokémon species is a single named entity in the Pokédex.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonSpeciesInVersionGroupRepository")
 *
 * @method PokemonSpecies getParent()
 * @method self setParent(PokemonSpecies $parent)
 */
class PokemonSpeciesInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var PokemonSpecies
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpecies", inversedBy="children")
     */
    protected $parent;

    /**
     * @var Collection|PokemonSpeciesPokedexNumber[]
     *
     * @ORM\OneToMany(targetEntity="PokemonSpeciesPokedexNumber", mappedBy="species", cascade={"ALL"},
     *     orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"isDefault" = "DESC"})
     */
    protected $numbers;

    /**
     * @var Collection|Pokemon[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="species", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\OrderBy({"isDefault" = "DESC"})
     */
    protected $pokemon;

    /**
     * PokemonSpeciesInVersionGroup constructor.
     */
    public function __construct()
    {
        $this->numbers = new ArrayCollection();
        $this->pokemon = new ArrayCollection();
    }

    /**
     * @return PokemonSpeciesPokedexNumber[]|Collection
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * @param PokemonSpeciesPokedexNumber $number
     *
     * @return self
     */
    public function addNumber(PokemonSpeciesPokedexNumber $number): self
    {
        if (!$this->numbers->contains($number)) {
            $this->numbers->add($number);
            $number->setSpecies($this);
        }

        return $this;
    }

    /**
     * @param PokemonSpeciesPokedexNumber $number
     *
     * @return self
     */
    public function removeNumber(PokemonSpeciesPokedexNumber $number): self
    {
        if ($this->numbers->contains($number)) {
            $this->numbers->removeElement($number);
        }

        return $this;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return self
     */
    public function addPokemon(Pokemon $pokemon): self
    {
        if (!$this->pokemon->contains($pokemon)) {
            $this->pokemon->add($pokemon);
            $pokemon->setSpecies($this);
        }

        return $this;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return self
     */
    public function removePokemon(Pokemon $pokemon): self
    {
        if ($this->pokemon->contains($pokemon)) {
            $this->pokemon->removeElement($pokemon);
        }

        return $this;
    }

    /**
     * @return Pokemon
     */
    public function getDefaultPokemon(): Pokemon
    {
        foreach ($this->getPokemon() as $pokemon) {
            if ($pokemon->isDefault()) {
                return $pokemon;
            }
        }

        return $this->getPokemon()->first();
    }

    /**
     * @return Pokemon[]|Collection
     */
    public function getPokemon()
    {
        return $this->pokemon;
    }
}
