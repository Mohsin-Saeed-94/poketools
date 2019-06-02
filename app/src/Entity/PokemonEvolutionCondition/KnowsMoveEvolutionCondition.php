<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\MoveInVersionGroup;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The Pokémon knows this move.
 *
 * @ORM\Entity()
 */
class KnowsMoveEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var MoveInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveInVersionGroup")
     * @Assert\NotBlank()
     */
    protected $knowsMove;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('Knows []{move:%s}', $this->getKnowsMove()->getSlug());
    }

    /**
     * @return MoveInVersionGroup
     */
    public function getKnowsMove(): ?MoveInVersionGroup
    {
        return $this->knowsMove;
    }

    /**
     * @param MoveInVersionGroup $knowsMove
     *
     * @return self
     */
    public function setKnowsMove(MoveInVersionGroup $knowsMove): self
    {
        $this->knowsMove = $knowsMove;

        return $this;
    }
}
