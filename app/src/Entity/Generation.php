<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Generation of the Pokémon franchise.
 *
 * @ORM\Entity(repositoryClass="App\Repository\GenerationRepository")
 */
class Generation extends AbstractDexEntity implements GroupableInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * Generation number
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    protected $number;

    /**
     * Version groups that are part of this generation
     *
     * @var VersionGroup[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VersionGroup", mappedBy="generation", fetch="EAGER")
     */
    protected $versionGroups;

    public function __construct()
    {
        $this->versionGroups = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return self
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return VersionGroup[]|Collection
     */
    public function getVersionGroups()
    {
        return $this->versionGroups;
    }

    /**
     * @param VersionGroup $versionGroup
     *
     * @return self
     */
    public function addVersionGroup(VersionGroup $versionGroup): self
    {
        if (!$this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->add($versionGroup);
            $versionGroup->setGeneration($this);
        }

        return $this;
    }

    /**
     * @param array|\iterable|VersionGroup[] $versionGroups
     *
     * @return self
     */
    public function addVersionGroups($versionGroups): self
    {
        foreach ($versionGroups as $versionGroup) {
            $this->addVersionGroup($versionGroup);
        }

        return $this;
    }

    /**
     * @param VersionGroup $versionGroup
     *
     * @return self
     */
    public function removeVersionGroup(VersionGroup $versionGroup): self
    {
        if ($this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->removeElement($versionGroup);
            $versionGroup->setGeneration(null);
        }

        return $this;
    }

    /**
     * @param array|\iterable|VersionGroup[] $versionGroups
     *
     * @return self
     */
    public function removeVersionGroups($versionGroups): self
    {
        foreach ($versionGroups as $versionGroup) {
            $this->removeVersionGroup($versionGroup);
        }

        return $this;
    }
}
