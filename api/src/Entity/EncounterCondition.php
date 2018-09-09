<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A condition in the game world that affects Pokémon encounters, such as time
 * of day.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterConditionRepository")
 */
class EncounterCondition extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var Collection|EncounterConditionState[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\EncounterConditionState", mappedBy="condition", cascade={"all"})
     * @Assert\NotBlank()
     */
    protected $states;

    /**
     * EncounterCondition constructor.
     */
    public function __construct()
    {
        $this->states = new ArrayCollection();
    }

    /**
     * @return EncounterConditionState[]|Collection
     */
    public function getStates(): Collection
    {
        return $this->states;
    }

    /**
     * @param EncounterConditionState[] $states
     *
     * @return self
     */
    public function addStates($states): self
    {
        foreach ($states as $state) {
            $this->addState($state);
        }

        return $this;
    }

    /**
     * @param EncounterConditionState $state
     *
     * @return self
     */
    public function addState(EncounterConditionState $state): self
    {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
            $state->setCondition($this);
        }

        return $this;
    }

    /**
     * @param EncounterConditionState[] $states
     *
     * @return EncounterCondition
     */
    public function removeStates($states): self
    {
        foreach ($states as $state) {
            $this->removeState($state);
            $state->setCondition(null);
        }

        return $this;
    }

    /**
     * @param EncounterConditionState $state
     *
     * @return self
     */
    public function removeState(EncounterConditionState $state): self
    {
        if ($this->states->contains($state)) {
            $this->states->removeElement($state);
        }

        return $this;
    }

    /**
     * @return EncounterConditionState|null
     */
    public function getDefaultState(): ?EncounterConditionState
    {
        $defaults = $this->states->filter(
            function (EncounterConditionState $state) {
                return $state->isDefault();
            }
        );

        if ($defaults->isEmpty()) {
            return null;
        } else {
            return $defaults->first();
        }
    }
}
