<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Default implementation of App\Entity\EntityHasIconInterface
 */
trait EntityHasIconTrait
{

    /**
     * Entity icon
     *
     * @var null|string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    protected $icon;

    /**
     * @return null|string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param null|string $icon
     *
     * @return self
     */
    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
