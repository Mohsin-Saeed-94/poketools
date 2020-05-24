<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An effect of a move.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveEffectRepository")
 */
class MoveEffect extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

    /**
     * Unique Id
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     */
    protected $id;

    /**
     * @var Collection|MoveEffectInVersionGroup[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MoveEffectInVersionGroup", mappedBy="parent", cascade={"all"}, fetch="EAGER")
     */
    protected $children;

    /**
     * Ability constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
