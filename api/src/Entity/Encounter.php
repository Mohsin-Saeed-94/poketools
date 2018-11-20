<?php

namespace App\Entity;

use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A single possible encounter with a pokemon
 *
 * This could be a pokemon encountered in the wild (e.g. tall grass), traded
 * or otherwise acquired from an NPC, or received as part of a scripted event.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterRepository")
 */
class Encounter extends AbstractDexEntity implements EntityGroupedByVersionInterface, EntityIsSortableInterface
{

    use EntityGroupedByVersionTrait;
    use EntityIsSortableTrait;

    /**
     * @var LocationArea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationArea")
     * @Assert\NotBlank()
     */
    protected $locationArea;

    /**
     * @var EncounterMethod
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EncounterMethod")
     * @Assert\NotBlank()
     */
    protected $method;

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon")
     * @Assert\NotBlank()
     */
    protected $pokemon;

    /**
     * @var Range|null
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Assert\NotBlank()
     */
    protected $level;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="1", max="100")
     */
    protected $chance;

    /**
     * @var EncounterConditionState|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EncounterConditionState", fetch="EAGER")
     */
    protected $conditions;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * Encounter constructor.
     */
    public function __construct()
    {
        $this->conditions = new ArrayCollection();
    }

    /**
     * @return LocationArea
     */
    public function getLocationArea(): ?LocationArea
    {
        return $this->locationArea;
    }

    /**
     * @param LocationArea $locationArea
     *
     * @return self
     */
    public function setLocationArea(LocationArea $locationArea): self
    {
        $this->locationArea = $locationArea;

        return $this;
    }

    /**
     * @return EncounterMethod
     */
    public function getMethod(): ?EncounterMethod
    {
        return $this->method;
    }

    /**
     * @param EncounterMethod $method
     *
     * @return self
     */
    public function setMethod(EncounterMethod $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return Pokemon
     */
    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return self
     */
    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * @return Range|null
     */
    public function getLevel(): ?Range
    {
        return $this->level;
    }

    /**
     * @param Range|null $level
     *
     * @return self
     */
    public function setLevel(?Range $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getChance(): ?int
    {
        return $this->chance;
    }

    /**
     * @param int|null $chance
     *
     * @return self
     */
    public function setChance(?int $chance): self
    {
        $this->chance = $chance;

        return $this;
    }

    /**
     * @return EncounterConditionState|Collection
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param EncounterConditionState $conditionState
     *
     * @return self
     */
    public function addCondition(EncounterConditionState $conditionState): self
    {
        if (!$this->conditions->contains($conditionState)) {
            $this->conditions->add($conditionState);
        }

        return $this;
    }

    /**
     * @param EncounterConditionState $conditionState
     *
     * @return self
     */
    public function removeCondition(EncounterConditionState $conditionState): self
    {
        if ($this->conditions->contains($conditionState)) {
            $this->conditions->removeElement($conditionState);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     *
     * @return self
     */
    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
