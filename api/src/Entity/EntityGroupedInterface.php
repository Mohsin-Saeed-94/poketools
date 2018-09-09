<?php


namespace App\Entity;

/**
 * Interface for entities that are grouped by a different entity.
 */
interface EntityGroupedInterface
{

    /**
     * Get the group this entity belongs to.
     *
     * @return GroupableInterface
     */
    public function getGroup(): GroupableInterface;

    /**
     * Get the name of the field used for grouping.
     *
     * @return string
     */
    public static function getGroupField(): string;
}
