<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A place in the Pokémon world.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 *
 * @method Location getParent()
 * @method self setParent(Location $parent)
 */
class LocationInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityGroupedByVersionGroupInterface
{

    use EntityHasParentTrait;
    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="children")
     */
    protected $parent;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Region")
     * @Assert\NotNull
     */
    protected $region;

    /**
     * @var LocationArea[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationArea", mappedBy="location", cascade={"ALL"}, fetch="EAGER")
     * @Assert\NotBlank
     */
    protected $areas;

    /**
     * Location constructor.
     */
    public function __construct()
    {
        $this->areas = new ArrayCollection();
    }

    /**
     * @return Region
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param Region $region
     *
     * @return self
     */
    public function setRegion(Region $region): self
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
}
