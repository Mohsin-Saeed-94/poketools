<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A Pokemon game version.
 *
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
     * @ORM\ManyToOne(targetEntity="App\Entity\VersionGroup", inversedBy="versions", fetch="EAGER")
     * @Assert\NotNull()
     */
    protected $versionGroup;
}
