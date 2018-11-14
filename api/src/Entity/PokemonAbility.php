<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonAbilityRepository")
 */
class PokemonAbility implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="abilities")
     * @ORM\Id()
     */
    protected $pokemon;

    /**
     * @var AbilityInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AbilityInVersionGroup")
     * @ORM\Id()
     */
    protected $ability;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $hidden = false;

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
     * @return AbilityInVersionGroup
     */
    public function getAbility(): ?AbilityInVersionGroup
    {
        return $this->ability;
    }

    /**
     * @param AbilityInVersionGroup $ability
     *
     * @return self
     */
    public function setAbility(AbilityInVersionGroup $ability): self
    {
        $this->ability = $ability;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     *
     * @return self
     */
    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }
}
