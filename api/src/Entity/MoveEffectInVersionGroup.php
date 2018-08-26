<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MoveEffectInVersionGroupRepository")
 */
class MoveEffectInVersionGroup extends AbstractDexEntity implements EntityGroupedByVersionGroupInterface, EntityHasParentInterface, EntityHasDescriptionInterface
{

    use EntityGroupedByVersionGroupTrait;
    use EntityHasParentTrait;
    use EntityHasDescriptionTrait;

    /**
     * @var Ability
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveEffect", inversedBy="children")
     */
    protected $parent;
}
