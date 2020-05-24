<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use App\Entity\TimeOfDay;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pokémon must evolve at a certain time of day.
 *
 * @ORM\Entity()
 */
class TimeOfDayEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var Collection|TimeOfDay[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\TimeOfDay")
     * @Assert\NotBlank()
     */
    protected $timesOfDay;

    /**
     * TimeOfDayEvolutionCondition constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->timesOfDay = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        $times = [];
        foreach ($this->getTimesOfDay() as $timeOfDay) {
            $times[] = $timeOfDay->getName();
        }

        return sprintf('During the %s', implode(', ', $times));
    }

    /**
     * @return Collection|TimeOfDay[]
     */
    public function getTimesOfDay(): Collection
    {
        return $this->timesOfDay;
    }

    /**
     * @param TimeOfDay $timeOfDay
     * @return self
     */
    public function addTimeOfDay(TimeOfDay $timeOfDay): self
    {
        if (!$this->timesOfDay->contains($timeOfDay)) {
            $this->timesOfDay->add($timeOfDay);
        }

        return $this;
    }

    /**
     * @param TimeOfDay $timeOfDay
     * @return self
     */
    public function removeTimeOfDay(TimeOfDay $timeOfDay): self
    {
        if ($this->timesOfDay->contains($timeOfDay)) {
            $this->timesOfDay->removeElement($timeOfDay);
        }

        return $this;
    }
}
