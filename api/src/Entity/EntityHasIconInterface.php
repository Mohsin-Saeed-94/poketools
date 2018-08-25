<?php


namespace App\Entity;

/**
 * Entities with icons
 */
interface EntityHasIconInterface
{

    /**
     * @return null|string
     */
    public function getIcon(): ?string;

    /**
     * @param null|string $image
     *
     * @return self
     */
    public function setIcon(?string $image);
}
