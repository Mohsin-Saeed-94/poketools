<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MoveEffectRepository")
 */
class MoveEffect extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * @var Collection|MoveEffectInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MoveEffectInVersionGroup", mappedBy="parent", cascade={"all"})
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
