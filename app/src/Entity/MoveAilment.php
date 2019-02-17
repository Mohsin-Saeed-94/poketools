<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Common status ailments moves can inflict on a single Pokémon, including major
 * ailments like paralysis and minor ailments like trapping.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveAilmentRepository")
 */
class MoveAilment extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityIsSortableTrait;

    /**
     * Does this ailment disappear after battle?
     *
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
