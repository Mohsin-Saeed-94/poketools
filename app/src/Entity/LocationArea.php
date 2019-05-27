<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A sub-area of a location. (e.g. 1F, Basement, etc.)
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationAreaRepository")
 */
class LocationArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;
    use EntityIsSortableTrait;

    /**
     * @var LocationInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationInVersionGroup", inversedBy="areas")
     * @Assert\NotNull
     */
    protected $location;

    public static function getGroupField(): string
    {
        return 'location';
    }

    /**
     * @return LocationInVersionGroup
     */
    public function getLocation(): ?LocationInVersionGroup
    {
        return $this->location;
    }

    /**
     * @param LocationInVersionGroup $location
     *
     * @return self
     */
    public function setLocation(?LocationInVersionGroup $location): self
    {
        $this->location = $location;

        return $this;
    }
}
