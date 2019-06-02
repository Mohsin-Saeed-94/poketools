<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\PokemonEvolutionCondition;
use App\Entity\Weather;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This weather must be present in the overworld; battle weather does not count.
 *
 * @ORM\Entity()
 */
class OverworldWeatherEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var Weather
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Weather")
     * @Assert\NotBlank()
     */
    protected $overworldWeather;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('[]{mechanic:%s} in the overworld', $this->getOverworldWeather()->getSlug());
    }

    /**
     * @return Weather
     */
    public function getOverworldWeather(): ?Weather
    {
        return $this->overworldWeather;
    }

    /**
     * @param Weather $overworldWeather
     *
     * @return self
     */
    public function setOverworldWeather(Weather $overworldWeather): self
    {
        $this->overworldWeather = $overworldWeather;

        return $this;
    }
}
