<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A collection of Pokémon species ordered in a particular way.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokedexRepository")
 */
class Pokedex extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityHasDefaultInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityHasDefaultTrait;

    /**
     * @var VersionGroup[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\VersionGroup")
     * @Assert\NotBlank()
     */
    protected $versionGroups;

    /**
     * Pokedex constructor.
     */
    public function __construct()
    {
        $this->versionGroups = new ArrayCollection();
    }

    /**
     * @return VersionGroup[]|Collection
     */
    public function getVersionGroups()
    {
        return $this->versionGroups;
    }

    /**
     * @param iterable $versionGroups
     *
     * @return self
     */
    public function addVersionGroups(iterable $versionGroups): self
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
    public function addVersionGroup(VersionGroup $versionGroup): self
    {
        if (!$this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->add($versionGroup);
        }

        return $this;
    }

    /**
     * @param $versionGroups
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

    /**
     * @param VersionGroup $versionGroup
     *
     * @return self
     */
    public function removeVersionGroup(VersionGroup $versionGroup): self
    {
        if ($this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->removeElement($versionGroup);
        }

        return $this;
    }
}
