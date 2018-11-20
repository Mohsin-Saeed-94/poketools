<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * The effect the move "Fling" has when used with an item.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemFlingEffectRepository")
 */
class ItemFlingEffect extends AbstractDexEntity implements EntityHasDescriptionInterface
{
    use EntityHasDescriptionTrait;
}
