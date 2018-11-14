<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonWildHeldItemRepository")
 */
class PokemonWildHeldItem
{

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="wildHeldItems")
     * @ORM\Id()
     */
    protected $pokemon;

    /**
     * @var Version
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Version")
     * @ORM\Id()
     */
    protected $version;

    /**
     * @var ItemInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup")
     * @ORM\Id()
     */
    protected $item;

    /**
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
     * @return Version
     */
    public function getVersion(): ?Version
    {
        return $this->version;
    }

    /**
     * @param Version $version
     *
     * @return self
     */
    public function setVersion(Version $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return ItemInVersionGroup
     */
    public function getItem(): ?ItemInVersionGroup
    {
        return $this->item;
    }

    /**
     * @param ItemInVersionGroup $item
     *
     * @return self
     */
    public function setItem(ItemInVersionGroup $item): self
    {
        $this->item = $item;

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
