<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The game console must be upside-down while evolution would be triggered.
 *
 * @ORM\Entity()
 */
class ConsoleInvertedEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Assert\NotNull()
     */
    protected $consoleInverted;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        if ($this->isConsoleInverted() === true) {
            return 'Console is upside-down';
        }

        return 'Console is not upside-down';
    }

    /**
     * @return bool
     */
    public function isConsoleInverted(): ?bool
    {
        return $this->consoleInverted;
    }

    /**
     * @param bool $consoleInverted
     *
     * @return self
     */
    public function setConsoleInverted(bool $consoleInverted): self
    {
        $this->consoleInverted = $consoleInverted;

        return $this;
    }
}
