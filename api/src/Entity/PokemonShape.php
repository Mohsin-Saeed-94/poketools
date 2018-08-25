<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PokemonShapeRepository")
 *
 * @method Collection|PokemonShapeInVersionGroup[] getChildren()
 * @method self addChild(PokemonShapeInVersionGroup $child)
 * @method self addChildren(Collection | PokemonShapeInVersionGroup $children)
 * @method self removeChild(PokemonShapeInVersionGroup $child)
 * @method self removeChildren(Collection | PokemonShapeInVersionGroup[] $children)
 */
class PokemonShape extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasChildrenInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasChildrenTrait;

    /**
     * @var Collection|PokemonShapeInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonShapeInVersionGroup", mappedBy="parent")
     */
    protected $children;
}
