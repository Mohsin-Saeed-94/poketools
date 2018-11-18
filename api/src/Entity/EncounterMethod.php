<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A way the player can enter a wild encounter.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterMethodRepository")
 */
class EncounterMethod extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
}
