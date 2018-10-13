<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stat changes moves (may) make.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveStatChangeRepository")
 */
class MoveStatChange
{

    /**
     * @var MoveInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveInVersionGroup")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $move;

    /**
     * @var Stat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $stat;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="-10", max="10")
     */
    protected $change;

    /**
     * @return MoveInVersionGroup
     */
    public function getMove(): MoveInVersionGroup
    {
        return $this->move;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function setMove(MoveInVersionGroup $move): self
    {
        $this->move = $move;

        return $this;
    }

    /**
     * @return Stat
     */
    public function getStat(): Stat
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
    public function getChange(): int
    {
        return $this->change;
    }

    /**
     * @param int $change
     *
     * @return self
     */
    public function setChange(int $change): self
    {
        $this->change = $change;

        return $this;
    }
}
