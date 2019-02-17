<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Berry flavor level.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryFlavorLevelRepository")
 */
class BerryFlavorLevel
{

    /**
     * @var Berry
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Berry", inversedBy="flavors")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $berry;

    /**
     * @var BerryFlavor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFlavor")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $flavor;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $level;

    /**
     * @return Berry
     */
    public function getBerry(): ?Berry
    {
        return $this->berry;
    }

    /**
     * @param Berry $berry
     *
     * @return self
     */
    public function setBerry(Berry $berry): self
    {
        $this->berry = $berry;

        return $this;
    }

    /**
     * @return BerryFlavor
     */
    public function getFlavor(): ?BerryFlavor
    {
        return $this->flavor;
    }

    /**
     * @param BerryFlavor $flavor
     *
     * @return self
     */
    public function setFlavor(BerryFlavor $flavor): self
    {
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return self
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }
}
