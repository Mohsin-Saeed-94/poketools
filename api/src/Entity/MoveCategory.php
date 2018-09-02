<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Very general categories that loosely group move effects.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveCategoryRepository")
 */
class MoveCategory extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
    use EntityHasDescriptionTrait;
}
