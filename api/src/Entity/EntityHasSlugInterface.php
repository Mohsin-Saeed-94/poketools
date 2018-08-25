<?php


namespace App\Entity;

/**
 * Entities that have slugs.
 */
interface EntityHasSlugInterface
{
    // These getters and setters don't type hint at all to be compatible with
    // Knp\DoctrineBehaviors\Model\Sluggable\Sluggable.

    /**
     * @return null|string
     */
    public function getSlug();

    /**
     * @param null|string $slug
     *
     * @return self
     */
    public function setSlug($slug);
}
