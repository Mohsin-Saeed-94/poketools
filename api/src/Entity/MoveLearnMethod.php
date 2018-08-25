<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MoveLearnMethodRepository")
 */
class MoveLearnMethod extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityIsSortableTrait;
}
