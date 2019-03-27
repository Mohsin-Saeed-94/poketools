<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonFlavorTextRepository")
 */
class PokemonFlavorText implements EntityHasFlavorTextInterface, EntityIsSortableInterface
{

    use EntityHasFlavorTextTrait;
    use EntityIsSortableTrait;

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="flavorText")
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
        $this->setPosition($version->getPosition());

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFlavorText() ?? '';
    }
}
