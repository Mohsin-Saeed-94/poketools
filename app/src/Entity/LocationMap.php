<?php

namespace App\Entity;

use App\Entity\Media\RegionMap;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationMapRepository")
 */
class LocationMap
{
    /**
     * @var LocationInVersionGroup
     *
     * @ORM\OneToOne(targetEntity="App\Entity\LocationInVersionGroup", inversedBy="map")
     * @ORM\Id()
     * @Assert\NotNull()
     */
    protected $location;

    /**
     * @var RegionMap
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\RegionMap")
     * @Assert\NotNull()
     */
    protected $map;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $overlay;

    /**
     * The order in which the overlays are drawn on top of each other.
     *
     * Higher numbers are on top of lower numbers.  The stacking relationship
     * between ovelrays with the same z-index is undefined.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     */
    protected $zIndex = 0;

    /**
     * @return LocationInVersionGroup
     */
    public function getLocation(): ?LocationInVersionGroup
    {
        return $this->location;
    }

    /**
     * @param LocationInVersionGroup $location
     *
     * @return self
     */
    public function setLocation(LocationInVersionGroup $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return RegionMap
     */
    public function getMap(): ?RegionMap
    {
        return $this->map;
    }

    /**
     * @param RegionMap $map
     *
     * @return self
     */
    public function setMap(RegionMap $map): self
    {
        $this->map = $map;

        return $this;
    }

    /**
     * @return string
     */
    public function getOverlay(): ?string
    {
        return $this->overlay;
    }

    /**
     * @param string $overlay
     *
     * @return self
     */
    public function setOverlay(string $overlay): self
    {
        $this->overlay = $overlay;

        return $this;
    }

    /**
     * @return int
     */
    public function getZIndex(): int
    {
        return $this->zIndex;
    }

    /**
     * @param int $zIndex
     *
     * @return self
     */
    public function setZIndex(int $zIndex): self
    {
        $this->zIndex = $zIndex;

        return $this;
    }
}
