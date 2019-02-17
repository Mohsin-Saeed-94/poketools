<?php


namespace App\Entity;

use Gedmo\Sluggable\Sluggable;

/**
 * Entities that have slugs.
 */
interface EntityHasSlugInterface extends Sluggable
{

    /**
     * @return null|string
     */
    public function getSlug(): ?string;

    /**
     * @param null|string $slug
     *
     * @return self
     */
    public function setSlug(?string $slug);
}
