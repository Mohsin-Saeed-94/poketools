<?php

namespace App\Entity;

use App\Entity\Embeddable\Range;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonFormPokeathlonStatRepository")
 */
class PokemonFormPokeathlonStat implements EntityIsSortableInterface
{

    use EntityIsSortableTrait;

    /**
     * @var PokemonForm
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonForm", inversedBy="pokeathlonStats")
     * @ORM\Id()
     */
    protected $pokemonForm;

    /**
     * @var PokeathlonStat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokeathlonStat")
     * @ORM\Id()
     */
    protected $pokeathlonStat;

    /**
     * @var Range
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Assert\NotBlank()
     * @Assert\Expression("value.getMin() >= 0 && value.getMax() <= 5")
     */
    protected $range;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="5")
     */
    protected $baseValue;

    /**
     * @return PokemonForm
     */
    public function getPokemonForm(): ?PokemonForm
    {
        return $this->pokemonForm;
    }

    /**
     * @param PokemonForm $pokemonForm
     *
     * @return self
     */
    public function setPokemonForm(PokemonForm $pokemonForm): self
    {
        $this->pokemonForm = $pokemonForm;

        return $this;
    }

    /**
     * @return PokeathlonStat
     */
    public function getPokeathlonStat(): ?PokeathlonStat
    {
        return $this->pokeathlonStat;
    }

    /**
     * @param PokeathlonStat $pokeathlonStat
     *
     * @return self
     */
    public function setPokeathlonStat(PokeathlonStat $pokeathlonStat): self
    {
        $this->pokeathlonStat = $pokeathlonStat;
        $this->setPosition($pokeathlonStat->getPosition());

        return $this;
    }

    /**
     * @return Range
     */
    public function getRange(): ?Range
    {
        return $this->range;
    }

    /**
     * @param Range $range
     *
     * @return self
     */
    public function setRange(Range $range): self
    {
        $this->range = $range;

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseValue(): ?int
    {
        return $this->baseValue;
    }

    /**
     * @param int $baseValue
     *
     * @return self
     */
    public function setBaseValue(int $baseValue): self
    {
        $this->baseValue = $baseValue;

        return $this;
    }
}
