<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AbilityInVersionGroupRepository")
 *
 * @method Ability getParent()
 * @method self setParent(Ability $parent)
 */
class AbilityInVersionGroup extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByVersionGroupInterface, EntityHasFlavorTextInterface, EntityHasDescriptionInterface, EntityHasParentInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasFlavorTextTrait;
    use EntityHasDescriptionTrait;
    use EntityHasParentTrait;

    /**
     * @var Ability
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Ability", inversedBy="children")
     */
    protected $parent;
}
