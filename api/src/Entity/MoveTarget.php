<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Targeting or “range” of a move, e.g. “Affects all opponents” or “Affects user”.
 *
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MoveTargetRepository")
 */
class MoveTarget extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
}
