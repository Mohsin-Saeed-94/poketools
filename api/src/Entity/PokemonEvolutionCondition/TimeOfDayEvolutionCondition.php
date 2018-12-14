<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use App\Entity\TimeOfDay;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pokémon must evolve at a certain time of day.
 *
 * @ORM\Entity()
 */
class TimeOfDayEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var TimeOfDay
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TimeOfDay")
     * @Assert\NotBlank()
     *
     * @Groups("read")
     */
    protected $timeOfDay;

    /**
     * @return TimeOfDay
     */
    public function getTimeOfDay(): ?TimeOfDay
    {
        return $this->timeOfDay;
    }

    /**
     * @param TimeOfDay $timeOfDay
     *
     * @return self
     */
    public function setTimeOfDay(TimeOfDay $timeOfDay): self
    {
        $this->timeOfDay = $timeOfDay;

        return $this;
    }
}
