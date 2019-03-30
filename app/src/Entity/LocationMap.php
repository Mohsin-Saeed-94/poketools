<?php

namespace App\Entity;

use App\Entity\Media\RegionMap;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="region_id", referencedColumnName="region_id"),
     *     @ORM\JoinColumn(name="map_url", referencedColumnName="url")
     * )
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
}
