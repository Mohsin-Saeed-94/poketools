<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Pokémon species is a single named entity in the Pokédex.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonSpeciesRepository")
 *
 * @method Collection|PokemonSpeciesInVersionGroup[] getChildren()
 * @method self addChild(PokemonSpeciesInVersionGroup $child)
 * @method self addChildren(Collection|PokemonSpeciesInVersionGroup $children)
 * @method self removeChild(PokemonSpeciesInVersionGroup $child)
 * @method self removeChildren(Collection|PokemonSpeciesInVersionGroup[] $children)
 * @method PokemonSpeciesInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class PokemonSpecies extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection|PokemonSpeciesInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonSpeciesInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected $children;

    /**
     * PokemonSpecies constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
