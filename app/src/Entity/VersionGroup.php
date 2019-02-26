<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A set of games that are part of the same release, differing only in trivial
 * ways (e.g. Pokemon available).
 *
 * @ORM\Entity(repositoryClass="App\Repository\VersionGroupRepository")
 */
class VersionGroup extends AbstractDexEntity implements GroupableInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByGenerationInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByGenerationTrait;
    use EntityIsSortableTrait;

    /**
     * The generation this version group belongs to
     *
     * @var Generation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Generation", inversedBy="versionGroups")
     * @Assert\NotNull()
     */
    protected $generation;

    /**
     * Versions in this version group
     *
     * @var Version[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Version", mappedBy="versionGroup")
     */
    protected $versions;

    /**
     * Regions available in this version group
     *
     * @var Region[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Region", inversedBy="versionGroups")
     */
    protected $regions;

    /**
     * A list of features in this version group
     *
     * @var Feature[]|Collection
     * @ORM\ManyToMany(targetEntity="App\Entity\Feature", fetch="EAGER")
     */
    protected $features;

    /**
     * VersionGroup constructor.
     */
    public function __construct()
    {
        $this->versions = new ArrayCollection();
        $this->regions = new ArrayCollection();
        $this->features = new ArrayCollection();
    }

    /**
     * @return Version[]|Collection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param array|\iterable|Version[] $versions
     *
     * @return self
     */
    public function addVersions($versions): self
    {
        foreach ($versions as $version) {
            $this->addVersion($version);
        }

        return $this;
    }

    /**
     * @param Version $version
     *
     * @return self
     */
    public function addVersion(Version $version): self
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setVersionGroup($this);
        }

        return $this;
    }

    /**
     * @param array|\iterable|Version[] $versions
     *
     * @return self
     */
    public function removeVersions($versions): self
    {
        foreach ($versions as $version) {
            $this->removeVersion($version);
        }

        return $this;
    }

    /**
     * @param Version $version
     *
     * @return self
     */
    public function removeVersion(Version $version): self
    {
        if ($this->versions->contains($version)) {
            $this->versions->removeElement($version);
            $version->setVersionGroup(null);
        }

        return $this;
    }

    /**
     * @return Region[]|Collection
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * @param array|\iterable|Region[] $regions
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
     * @param array|\iterable|Region[] $regions
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
     * @param Feature $feature
     *
     * @return self
     */
    public function addFeature(Feature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
        }

        return $this;
    }

    /**
     * @param Feature $feature
     *
     * @return self
     */
    public function removeFeature(Feature $feature): self
    {
        if ($this->features->contains($feature)) {
            $this->features->removeElement($feature);
        }

        return $this;
    }

    /**
     * @return Feature[]|Collection
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param string $feature
     *
     * @return bool
     */
    public function hasFeatureString(string $feature): bool
    {
        return $this->features->exists(
            function ($key, Feature $value) use ($feature) {
                return $value->getSlug() === $feature;
            }
        );
    }
}
