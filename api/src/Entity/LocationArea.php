<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A sub-area of a location. (e.g. 1F, Basement, etc.)
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationAreaRepository")
 */
class LocationArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;

    /**
     * @var LocationInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationInVersionGroup")
     * @Assert\NotNull
     */
    protected $location;

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

    public static function getGroupField(): string
    {
        return 'location';
    }
}
