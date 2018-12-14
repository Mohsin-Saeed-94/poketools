<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\Gender;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * The Pokémon must be this gender.
 *
 * @ORM\Entity()
 */
class GenderEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var Gender
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Gender")
     *
     * @Groups("read")
     */
    protected $gender;

    /**
     * @return Gender
     */
    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    /**
     * @param Gender $gender
     *
     * @return self
     */
    public function setGender(Gender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }
}
