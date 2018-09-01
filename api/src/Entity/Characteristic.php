<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\CharacteristicRepository")
 */
class Characteristic extends AbstractDexEntity implements EntityHasFlavorTextInterface
{

    use EntityHasFlavorTextTrait;

    /**
     * @var Stat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat")
     * @Assert\NotNull()
     */
    protected $stat;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $ivDeterminator;

    /**
     * @return Stat
     */
    public function getStat(): ?Stat
    {
        return $this->stat;
    }

    /**
     * @param Stat $stat
     *
     * @return self
     */
    public function setStat(Stat $stat): self
    {
        $this->stat = $stat;

        return $this;
    }

    /**
     * @return int
     */
    public function getIvDeterminator(): ?int
    {
        return $this->ivDeterminator;
    }

    /**
     * @param int $ivDeterminator
     *
     * @return self
     */
    public function setIvDeterminator(int $ivDeterminator): self
    {
        $this->ivDeterminator = $ivDeterminator;

        return $this;
    }
}
