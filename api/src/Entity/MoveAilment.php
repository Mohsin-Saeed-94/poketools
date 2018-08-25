<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MoveAilmentRepository")
 */
class MoveAilment extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityIsSortableTrait;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $volatile = false;

    /**
     * @return bool
     */
    public function isVolatile(): bool
    {
        return $this->volatile;
    }

    /**
     * @param bool $volatile
     *
     * @return self
     */
    public function setVolatile(bool $volatile): self
    {
        $this->volatile = $volatile;

        return $this;
    }
}
