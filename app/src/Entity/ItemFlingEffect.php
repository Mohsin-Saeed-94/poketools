<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The effect the move "Fling" has when used with an item.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemFlingEffectRepository")
 */
class ItemFlingEffect extends AbstractDexEntity implements EntityHasDescriptionInterface
{
    use EntityHasDescriptionTrait;
}
