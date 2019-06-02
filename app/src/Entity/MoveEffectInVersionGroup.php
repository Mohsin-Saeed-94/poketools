<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An effect of a move.
 *
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
