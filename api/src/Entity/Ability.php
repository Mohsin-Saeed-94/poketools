<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AbilityRepository")
 *
 * @method Collection|AbilityInVersionGroup[] getChildren()
 * @method self addChild(AbilityInVersionGroup $child)
 * @method self addChildren(Collection | AbilityInVersionGroup $children)
 * @method self removeChild(AbilityInVersionGroup $child)
 * @method self removeChildren(Collection | AbilityInVersionGroup[] $children)
 * @method AbilityInVersionGroup findChildByGrouping(VersionGroup $group)
 */
class Ability extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasChildrenInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasChildrenTrait;

    /**
     * @var Collection|AbilityInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AbilityInVersionGroup", mappedBy="parent", cascade={"all"})
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
