<?php


namespace App\Entity;

/**
 * Interface for entities that can be used as groups for other entities.
 */
interface GroupableInterface
{

    /**
     * @return string
     */
    public function getSlug(): ?string;
}
