<?php


namespace App\Entity;

/**
 * Entity has a description.
 */
interface EntityHasDescriptionInterface
{

    /**
     * @return null|string
     */
    public function getShortDescription(): ?string;

    /**
     * @param null|string $shortDescription
     *
     * @return mixed
     */
    public function setShortDescription(?string $shortDescription);

    /**
     * @return null|string
     */
    public function getDescription(): ?string;

    /**
     * @param null|string $description
     *
     * @return self
     */
    public function setDescription(?string $description);
}
