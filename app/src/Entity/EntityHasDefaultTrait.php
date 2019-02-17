<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Default implementation of EntityHasDefaultInterface
 */
trait EntityHasDefaultTrait
{

    /**
     * Is this the default entity?
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isDefault = false;

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     *
     * @return self
     */
    public function setDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }
}
