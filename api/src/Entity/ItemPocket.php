<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A pocket in the in-game bag.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemPocketRepository")
 */
class ItemPocket extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection|ItemPocketInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ItemPocketInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected $children;

    /**
     * Ability constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
}
