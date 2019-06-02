<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A possible state for a condition.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EncounterConditionStateRepository")
 */
class EncounterConditionState extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface, EntityHasDefaultInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
    use EntityHasDefaultTrait;

    /**
     * The encounter condition this state belongs to
     *
     * @var EncounterCondition
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EncounterCondition", inversedBy="states")
     */
    protected $condition;

    /**
     * @return EncounterCondition
     */
    public function getCondition(): ?EncounterCondition
    {
        return $this->condition;
    }

    /**
     * @param EncounterCondition $condition
     *
     * @return self
     */
    public function setCondition(?EncounterCondition $condition): self
    {
        $this->condition = $condition;

        return $this;
    }
}
