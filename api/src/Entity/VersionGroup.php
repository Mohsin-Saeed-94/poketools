<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\VersionGroupRepository")
 */
class VersionGroup extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByGenerationInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByGenerationTrait;
    use EntityIsSortableTrait;
}
