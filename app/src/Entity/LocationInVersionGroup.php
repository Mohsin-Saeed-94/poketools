<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A place in the Pokémon world.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationInVersionGroupRepository")
 * @Gedmo\Tree(type="materializedPath")
 *
 * @method Location getParent()
 * @method self setParent(Location $parent)
 */
class LocationInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByVersionGroupInterface, EntityHasDescriptionInterface
{

    use EntityHasParentTrait;
    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasDescriptionTrait;

    /**
     * Unique Id
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Gedmo\TreePathSource()
     */
    protected $id;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="children")
     */
    protected $parent;

    /**
     * @var RegionInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RegionInVersionGroup")
     * @Assert\NotNull
     */
    protected $region;

    /**
     * @var LocationArea[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationArea", mappedBy="location", cascade={"ALL"}, orphanRemoval=true,
     *     fetch="EAGER")
     * @ORM\OrderBy({"position": "ASC"})
     * @Assert\NotBlank
     */
    protected $areas;

    /**
     * @var LocationMap|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\LocationMap", mappedBy="location", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $map;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    protected $treePath;

    /**
     * The location that contains this one (e.g. The Tin Tower is inside Ecruteak city,
     * this would be the relevant Ecruteak city location entity).
     *
     * @var LocationInVersionGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationInVersionGroup", inversedBy="subLocations")
     * @Gedmo\TreeParent()
     */
    protected $superLocation;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeLevel()
     */
    protected $treeLevel;

    /**
     * @var LocationInVersionGroup[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationInVersionGroup", mappedBy="superLocation")
     * @ORM\OrderBy({"name": "ASC"})
     */
    protected $subLocations;

    /**
     * Location constructor.
     */
    public function __construct()
    {
        $this->areas = new ArrayCollection();
        $this->subLocations = new ArrayCollection();
    }

    /**
     * @return RegionInVersionGroup
     */
    public function getRegion(): ?RegionInVersionGroup
    {
        return $this->region;
    }

    /**
     * @param RegionInVersionGroup $region
     *
     * @return self
     */
    public function setRegion(RegionInVersionGroup $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection|LocationArea[]
     */
    public function getAreas(): Collection
    {
        return $this->areas;
    }

    /**
     * @param LocationArea[] $areas
     *
     * @return self
     */
    public function addAreas($areas)
    {
        foreach ($areas as $area) {
            $this->addArea($area);
        }

        return $this;
    }

    /**
     * @param LocationArea $area
     *
     * @return self
     */
    public function addArea(LocationArea $area)
    {
        if (!$this->areas->contains($area)) {
            $this->areas->add($area);
            $area->setLocation($this);
        }

        return $this;
    }

    /**
     * @param LocationArea[] $areas
     *
     * @return self
     */
    public function removeAreas($areas)
    {
        foreach ($areas as $area) {
            $this->removeArea($area);
        }

        return $this;
    }

    /**
     * @param LocationArea $child
     *
     * @return self
     */
    public function removeArea(LocationArea $child)
    {
        if ($this->areas->contains($child)) {
            $this->areas->removeElement($child);
            $child->setLocation(null);
        }

        return $this;
    }

    /**
     * @return LocationMap|null
     */
    public function getMap(): ?LocationMap
    {
        return $this->map;
    }

    /**
     * @param LocationMap|null $map
     *
     * @return self
     */
    public function setMap(?LocationMap $map): self
    {
        $this->map = $map;
        if ($map !== null) {
            $map->setLocation($this);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTreePath(): ?string
    {
        return $this->treePath;
    }

    /**
     * @param string|null $treePath
     *
     * @return self
     */
    public function setTreePath(?string $treePath): self
    {
        $this->treePath = $treePath;

        return $this;
    }

    /**
     * @return LocationInVersionGroup|null
     */
    public function getSuperLocation(): ?LocationInVersionGroup
    {
        return $this->superLocation;
    }

    /**
     * @param LocationInVersionGroup|null $superLocation
     *
     * @return self
     */
    public function setSuperLocation(?LocationInVersionGroup $superLocation): self
    {
        $this->superLocation = $superLocation;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTreeLevel(): ?int
    {
        return $this->treeLevel;
    }

    /**
     * @return LocationInVersionGroup[]|Collection
     */
    public function getSubLocations()
    {
        return $this->subLocations;
    }

    /**
     * @param LocationInVersionGroup $subLocation
     *
     * @return self
     */
    public function addSubLocation(LocationInVersionGroup $subLocation): self
    {
        if (!$this->subLocations->contains($subLocation)) {
            $this->subLocations->add($subLocation);
            $subLocation->setSuperLocation($this);
        }

        return $this;
    }

    /**
     * @param LocationInVersionGroup $subLocation
     *
     * @return self
     */
    public function removeSubLocation(LocationInVersionGroup $subLocation): self
    {
        if ($this->subLocations->contains($subLocation)) {
            $this->subLocations->removeElement($subLocation);
            $subLocation->setSuperLocation(null);
        }

        return $this;
    }
}
