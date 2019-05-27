<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A pocket in the in-game bag.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemPocketInVersionGroupRepository")
 */
class ItemPocketInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityHasIconInterface, EntityIsSortableInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityHasIconTrait;
    use EntityIsSortableTrait;

    /**
     * @var ItemPocket
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemPocket", inversedBy="children")
     */
    protected $parent;
}
