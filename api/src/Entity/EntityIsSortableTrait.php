<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Default implementation of App\Entity\EntityIsSortableInterface
 */
trait EntityIsSortableTrait
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $position = 0;

    /**
     * @return int
     */
    public function getPosition(): int
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
