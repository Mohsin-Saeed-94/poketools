<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * An ability a Pokémon can have, such as Static or Pressure.
 *
 * @ORM\Entity(repositoryClass="App\Repository\AbilityRepository")
 *
 * @method Collection|AbilityInVersionGroup[] getChildren()
 * @method self addChild(AbilityInVersionGroup $child)
 * @method self addChildren(Collection | AbilityInVersionGroup $children)
 * @method self removeChild(AbilityInVersionGroup $child)
 * @method self removeChildren(Collection | AbilityInVersionGroup[] $children)
 * @method AbilityInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class Ability extends AbstractDexEntity implements EntityHasChildrenInterface
{
    use EntityHasChildrenTrait;

    /**
     * @var Collection|AbilityInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AbilityInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected $children;

    /**
     * Ability constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
