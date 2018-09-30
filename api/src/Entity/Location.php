<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A place in the Pokémon world.
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationInVersionGroupRepository")
 */
class Location extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection|LocationInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationInVersionGroup", mappedBy="parent", cascade={"all"})
     */
    protected $children;

    /**
     * Location constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
