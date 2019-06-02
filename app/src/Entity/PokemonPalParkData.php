<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data for the Pal Park mini-game in Generation IV.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonPalParkDataRepository")
 */
class PokemonPalParkData
{

    /**
     * @var Pokemon
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Pokemon", inversedBy="palParkData")
     * @ORM\Id()
     */
    protected $pokemon;

    /**
     * @var PalParkArea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PalParkArea", fetch="EAGER")
     * @ORM\Id()
     */
    protected $area;

    /**
     * Used in calculating the player’s score at the end of a Pal Park run
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="100")
     */
    protected $score;

    /**
     * Base rate for encountering this Pokémon
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="100")
     */
    protected $rate;

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
     * @return PalParkArea
     */
    public function getArea(): ?PalParkArea
    {
        return $this->area;
    }

    /**
     * @param PalParkArea $area
     *
     * @return self
     */
    public function setArea(PalParkArea $area): self
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return int
     */
    public function getScore(): ?int
    {
        return $this->score;
    }

    /**
     * @param int $score
     *
     * @return self
     */
    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return int
     */
    public function getRate(): ?int
    {
        return $this->rate;
    }

    /**
     * @param int $rate
     *
     * @return self
     */
    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }
}
