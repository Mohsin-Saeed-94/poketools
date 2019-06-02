<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * No conditions are required; trigger will always work.
 *
 * @ORM\Entity()
 */
class NoConditionsEvolutionCondition extends PokemonEvolutionCondition
{

}
