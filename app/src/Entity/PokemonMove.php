<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonMoveRepository")
 */
class PokemonMove extends AbstractDexEntity implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="moves")
     */
    protected $pokemon;

    /**
     * @var MoveInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveInVersionGroup")
     */
    protected $move;

    /**
     * @var MoveLearnMethod
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveLearnMethod")
     */
    protected $learnMethod;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     */
    protected $level;

    /**
     * @var ItemInVersionGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup")
     */
    protected $machine;

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
     * @return MoveInVersionGroup
     */
    public function getMove(): ?MoveInVersionGroup
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
     * @return MoveLearnMethod
     */
    public function getLearnMethod(): ?MoveLearnMethod
    {
        return $this->learnMethod;
    }

    /**
     * @param MoveLearnMethod $learnMethod
     *
     * @return self
     */
    public function setLearnMethod(MoveLearnMethod $learnMethod): self
    {
        $this->learnMethod = $learnMethod;

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
    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return ItemInVersionGroup
     */
    public function getMachine(): ?ItemInVersionGroup
    {
        return $this->machine;
    }

    /**
     * @param ItemInVersionGroup $machine
     *
     * @return self
     */
    public function setMachine(?ItemInVersionGroup $machine): self
    {
        $this->machine = $machine;

        return $this;
    }
}
