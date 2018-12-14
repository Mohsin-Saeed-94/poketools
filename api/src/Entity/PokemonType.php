<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonTypeRepository")
 */
class PokemonType implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="types")
     * @ORM\Id()
     */
    protected $pokemon;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type", fetch="EAGER")
     * @ORM\Id()
     *
     * @Groups("read")
     */
    protected $type;

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
     * @return Type
     */
    public function getType(): ?Type
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return self
     */
    public function setType(Type $type): self
    {
        $this->type = $type;

        return $this;
    }
}
