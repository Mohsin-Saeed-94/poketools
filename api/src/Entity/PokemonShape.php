<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PokemonShapeRepository")
 *
 * @method Collection|PokemonShapeInVersionGroup[] getChildren()
 * @method self addChild(PokemonShapeInVersionGroup $child)
 * @method self addChildren(Collection | PokemonShapeInVersionGroup $children)
 * @method self removeChild(PokemonShapeInVersionGroup $child)
 * @method self removeChildren(Collection | PokemonShapeInVersionGroup[] $children)
 * @method PokemonShapeInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class PokemonShape extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection|PokemonShapeInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonShapeInVersionGroup", mappedBy="parent", cascade={"all"})
     */
    protected $children;

    /**
     * PokemonShape constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
