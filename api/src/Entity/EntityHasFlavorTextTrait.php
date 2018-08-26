<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Default implementation of App\Entity\EntityHasFlavorTextInterface
 */
trait EntityHasFlavorTextTrait
{

    /**
     * @var null|string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $flavorText;

    /**
     * @return null|string
     */
    public function getFlavorText(): ?string
    {
        return $this->flavorText;
    }

    /**
     * @param null|string $flavorText
     *
     * @return self
     */
    public function setFlavorText(?string $flavorText): self
    {
        if ($flavorText === '') {
            $this->flavorText = null;
        } else {
            $this->flavorText = $flavorText;
        }

        return $this;
    }
}
