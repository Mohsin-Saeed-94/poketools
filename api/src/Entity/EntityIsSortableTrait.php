<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;

/**
 * Default implementation of App\Entity\EntityIsSortableInterface
 */
trait EntityIsSortableTrait
{

    /**
     * Sort position
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @ApiProperty(iri="http://schema.org/position")
     */
    protected $position = 0;

    /**
     * @return int
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return self
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
