<?php


namespace App\Entity;

/**
 * Entity has in-game flavor text.
 */
interface EntityHasFlavorTextInterface
{

    /**
     * @return null|string
     */
    public function getFlavorText(): ?string;

    /**
     * @param null|string $flavorText
     *
     * @return self
     */
    public function setFlavorText(?string $flavorText);
}
