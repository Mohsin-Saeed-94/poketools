<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonStatRepository")
 */
class PokemonStat implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="stats")
     * @ORM\Id()
     */
    protected $pokemon;

    /**
     * @var Stat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat")
     * @ORM\Id()
     */
    protected $stat;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="255")
     */
    protected $baseValue;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="-255", max="255")
     */
    protected $effortChange;

    /**
     * @return Pokemon
     */
    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return self
     */
    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

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
        $this->setPosition($stat->getPosition());

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseValue(): ?int
    {
        return $this->baseValue;
    }

    /**
     * @param int $baseValue
     *
     * @return self
     */
    public function setBaseValue(int $baseValue): self
    {
        $this->baseValue = $baseValue;

        return $this;
    }

    /**
     * @return int
     */
    public function getEffortChange(): ?int
    {
        return $this->effortChange;
    }

    /**
     * @param int $effortChange
     *
     * @return self
     */
    public function setEffortChange(int $effortChange): self
    {
        $this->effortChange = $effortChange;

        return $this;
    }
}
