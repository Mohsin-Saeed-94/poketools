<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Specifies how a Nature affects a Pokéathlon stat.
 *
 * @ORM\Entity(repositoryClass="App\Repository\NaturePokeathlonStatChangeRepository")
 */
class NaturePokeathlonStatChange
{

    /**
     * @var Nature
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Nature", inversedBy="pokeathlonStatChanges")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $nature;

    /**
     * @var PokeathlonStat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokeathlonStat")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $pokeathlonStat;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="-5", max="5")
     */
    protected $maxChange;

    /**
     * @return Nature
     */
    public function getNature(): ?Nature
    {
        return $this->nature;
    }

    /**
     * @param Nature $nature
     *
     * @return self
     */
    public function setNature(?Nature $nature): self
    {
        $this->nature = $nature;

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

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxChange(): ?int
    {
        return $this->maxChange;
    }

    /**
     * @param int $maxChange
     *
     * @return self
     */
    public function setMaxChange(int $maxChange): self
    {
        $this->maxChange = $maxChange;

        return $this;
    }
}
