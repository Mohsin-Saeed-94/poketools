<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An effect of a move.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveEffectRepository")
 */
class MoveEffect extends AbstractDexEntity implements EntityHasChildrenInterface
{

    use EntityHasChildrenTrait;

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
}
