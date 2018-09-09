<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityGroupedByVersionGroupInterface
 */
trait EntityGroupedByVersionGroupTrait
{

    /**
     * @var VersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VersionGroup")
     * @Assert\NotNull()
     */
    protected $versionGroup;

    /**
     * @return VersionGroup
     */
    public function getVersionGroup(): ?VersionGroup
    {
        return $this->versionGroup;
    }

    /**
     * @param VersionGroup $versionGroup
     *
     * @return self
     */
    public function setVersionGroup(?VersionGroup $versionGroup): self
    {
        $this->versionGroup = $versionGroup;

        return $this;
    }

    public function getGroup():GroupableInterface {
        return $this->getVersionGroup();
    }

    public static function getGroupField(): string
    {
        return 'versionGroup';
    }
}
