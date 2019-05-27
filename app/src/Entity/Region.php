<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Major areas of the world: Kanto, Johto, etc.
 *
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 *
 * @method Collection|RegionInVersionGroup[] getChildren()
 * @method self addChild(RegionInVersionGroup $child)
 * @method self addChildren(Collection | RegionInVersionGroup $children)
 * @method self removeChild(RegionInVersionGroup $child)
 * @method self removeChildren(Collection | RegionInVersionGroup[] $children)
 * @method RegionInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class Region extends AbstractDexEntity implements EntityHasChildrenInterface
{
    use EntityHasChildrenTrait;

    /**
     * @var Collection|RegionInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\RegionInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected $children;

    /**
     * Region constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
