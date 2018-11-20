<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * An ability a Pokémon can have, such as Static or Pressure.
 *
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
