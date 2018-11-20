<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Targeting or “range” of a move, e.g. “Affects all opponents” or “Affects user”.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveTargetRepository")
 */
class MoveTarget extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
}
