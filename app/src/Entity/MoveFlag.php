<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Move attribute such as "snatchable" or "contact".
 *
 * @todo Make these tied to version groups.  Many don't apply to early versions.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveFlagRepository")
 */
class MoveFlag extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;

}
