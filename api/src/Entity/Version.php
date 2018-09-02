<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\VersionRepository")
 */
class Version extends AbstractDexEntity implements GroupableInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByVersionGroupInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityIsSortableTrait;

    /**
     * The Version group this Version belongs to
     *
     * @var VersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VersionGroup", inversedBy="versions")
     * @Assert\NotNull()
     */
    protected $versionGroup;
}
