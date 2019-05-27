<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A place in the Pokémon world.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 *
 * @method Collection|LocationInVersionGroup[] getChildren()
 * @method self addChild(LocationInVersionGroup $child)
 * @method self addChildren(Collection|LocationInVersionGroup $children)
 * @method self removeChild(LocationInVersionGroup $child)
 * @method self removeChildren(Collection|LocationInVersionGroup[] $children)
 * @method LocationInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class Location extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection|LocationInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected $children;

    /**
     * Location constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
