<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A place in the Pokémon world.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationInVersionGroupRepository")
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
     * Location constructor.
     */
    public function __construct()
    {
        $this->areas = new ArrayCollection();
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
}
