<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Move attribute such as “snatchable” or “contact”.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveFlagRepository")
 */
class MoveFlag extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;

}
