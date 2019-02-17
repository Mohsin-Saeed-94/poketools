<?php


namespace App\Entity;

/**
 * Entity could be the default in a group of related entities.
 */
interface EntityHasDefaultInterface
{

    /**
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * @param bool $default
     *
     * @return self
     */
    public function setDefault(bool $default);
}
