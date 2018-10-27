<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Item from the games, like “Poké Ball” or “Bicycle”.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 *
 * @method Collection|ItemInVersionGroup[] getChildren()
 * @method self addChild(ItemInVersionGroup $child)
 * @method self addChildren(Collection | ItemInVersionGroup $children)
 * @method self removeChild(ItemInVersionGroup $child)
 * @method self removeChildren(Collection | ItemInVersionGroup[] $children)
 * @method ItemInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class Item extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection|ItemInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ItemInVersionGroup", mappedBy="parent", cascade={"all"})
     */
    protected $children;

    /**
     * Item constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
