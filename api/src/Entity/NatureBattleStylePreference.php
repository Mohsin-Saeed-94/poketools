<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Specifies how likely a Pokémon with a specific Nature is to use a move of a
 * particular battle style in Battle Palace or Battle Tent.
 *
 * @ORM\Entity(repositoryClass="App\Repository\NatureBattleStylePreferenceRepository")
 */
class NatureBattleStylePreference
{

    /**
     * @var Nature
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Nature", inversedBy="battleStylePreferences")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $nature;

    /**
     * @var BattleStyle
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BattleStyle")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $battleStyle;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="100")
     */
    protected $lowHpChance;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="100")
     */
    protected $highHpChance;

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
     * @return BattleStyle
     */
    public function getBattleStyle(): ?BattleStyle
    {
        return $this->battleStyle;
    }

    /**
     * @param BattleStyle $battleStyle
     *
     * @return self
     */
    public function setBattleStyle(BattleStyle $battleStyle): self
    {
        $this->battleStyle = $battleStyle;

        return $this;
    }

    /**
     * @return int
     */
    public function getLowHpChance(): ?int
    {
        return $this->lowHpChance;
    }

    /**
     * @param int $lowHpChance
     *
     * @return self
     */
    public function setLowHpChance(int $lowHpChance): self
    {
        $this->lowHpChance = $lowHpChance;

        return $this;
    }

    /**
     * @return int
     */
    public function getHighHpChance(): ?int
    {
        return $this->highHpChance;
    }

    /**
     * @param int $highHpChance
     *
     * @return self
     */
    public function setHighHpChance(int $highHpChance): self
    {
        $this->highHpChance = $highHpChance;

        return $this;
    }
}
