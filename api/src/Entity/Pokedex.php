<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A collection of Pokémon species ordered in a particular way.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokedexRepository")
 */
class Pokedex extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;

    /**
     * @var Region[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Region")
     * @Assert\NotBlank()
     */
    protected $regions;

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
        $this->regions = new ArrayCollection();
        $this->versionGroups = new ArrayCollection();
    }

    /**
     * @return Region[]|Collection
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * @param $regions
     *
     * @return self
     */
    public function addRegions($regions): self
    {
        foreach ($regions as $region) {
            $this->addRegion($region);
        }

        return $this;
    }

    /**
     * @param Region $region
     *
     * @return self
     */
    public function addRegion(Region $region): self
    {
        if (!$this->regions->contains($region)) {
            $this->regions->add($region);
        }

        return $this;
    }

    /**
     * @param $regions
     *
     * @return self
     */
    public function removeRegions($regions): self
    {
        foreach ($regions as $region) {
            $this->removeRegion($region);
        }

        return $this;
    }

    /**
     * @param Region $region
     *
     * @return self
     */
    public function removeRegion(Region $region): self
    {
        if ($this->regions->contains($region)) {
            $this->regions->removeElement($region);
        }

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
