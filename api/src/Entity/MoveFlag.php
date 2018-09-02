<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Move attribute such as “snatchable” or “contact”.
 *
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MoveFlagRepository")
 */
class MoveFlag extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;

}
