<?php


namespace App\Entity;

/**
 * Entities with a given sort order.
 */
interface EntityIsSortableInterface
{

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $position
     *
     * @return self
     */
    public function setPosition(int $position);
}
