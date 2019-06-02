<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use App\Entity\Type;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pokémon knows a move of this type
 *
 * @ORM\Entity()
 */
class KnowsMoveTypeEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @Assert\NotBlank()
     */
    protected $knowsMoveType;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('Knows a []{type:%s}-type move', $this->getKnowsMoveType()->getSlug());
    }

    /**
     * @return Type
     */
    public function getKnowsMoveType(): ?Type
    {
        return $this->knowsMoveType;
    }

    /**
     * @param Type $knowsMoveType
     *
     * @return self
     */
    public function setKnowsMoveType(Type $knowsMoveType): self
    {
        $this->knowsMoveType = $knowsMoveType;

        return $this;
    }
}
