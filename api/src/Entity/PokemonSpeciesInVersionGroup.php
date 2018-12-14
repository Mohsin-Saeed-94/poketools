<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * A Pokémon species is a single named entity in the Pokédex.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonSpeciesInVersionGroupRepository")
 *
 * @method PokemonSpecies getParent()
 * @method self setParent(PokemonSpecies $parent)
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true}
 * )
 * @ApiFilter(SearchFilter::class, properties={"versionGroup": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"position": "ASC"})
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
     * @ORM\OneToMany(targetEntity="PokemonSpeciesPokedexNumber", mappedBy="species", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"isDefault" = "DESC"})
     *
     * @Groups("read")
     */
    protected $numbers;

    /**
     * @var Collection|Pokemon[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="species", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\OrderBy({"isDefault" = "DESC"})
     *
     * @ApiSubresource()
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
     * @return Pokemon[]|Collection
     */
    public function getPokemon()
    {
        return $this->pokemon;
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
}
