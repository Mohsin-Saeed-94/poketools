<?php

namespace App\Entity;

use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Time of Day
 *
 * @ORM\Entity(repositoryClass="App\Repository\TimeOfDayRepository")
 */
class TimeOfDay extends AbstractDexEntity implements EntityGroupedByGenerationInterface, EntityIsSortableInterface, EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityGroupedByGenerationTrait;
    use EntityIsSortableTrait;
    use EntityHasNameAndSlugTrait;

    /**
     * @var Chronos
     *
     * @ORM\Column(type="chronos_time")
     * @Assert\NotBlank()
     */
    protected $starts;

    /**
     * @var Chronos
     *
     * @ORM\Column(type="chronos_time")
     * @Assert\NotBlank()
     */
    protected $ends;

    /**
     * @return Chronos
     */
    public function getStarts(): ?Chronos
    {
        return $this->starts;
    }

    /**
     * @param Chronos $starts
     *
     * @return self
     */
    public function setStarts(Chronos $starts): self
    {
        $this->starts = $starts;

        return $this;
    }

    /**
     * @return Chronos
     */
    public function getEnds(): ?Chronos
    {
        return $this->ends;
    }

    /**
     * @param Chronos $ends
     *
     * @return self
     */
    public function setEnds(Chronos $ends): self
    {
        $this->ends = $ends;

        return $this;
    }
}
