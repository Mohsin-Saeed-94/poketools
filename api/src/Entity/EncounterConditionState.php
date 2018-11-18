<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
     * URL slug
     *
     * @var string|null
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(fields={"name"}, handlers={@Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\RelativeSlugHandler", options={
     *       @Gedmo\SlugHandlerOption(name="relationField", value="condition"),
     *       @Gedmo\SlugHandlerOption(name="relationSlugField", value="slug")
     *     })})
     */
    protected $slug;

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
