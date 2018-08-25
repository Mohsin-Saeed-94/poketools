<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityHasDescriptionTrait
{

    /**
     * @var null|string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @var null|string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @return null|string
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param null|string $shortDescription
     *
     * @return self
     */
    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
