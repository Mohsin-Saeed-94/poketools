<?php


namespace App\Entity;

/**
 * Entities that have names.
 */
interface EntityHasNameInterface
{

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name);
}
