<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Effect of a move when used in a Contest.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContestEffectInVersionGroupRepository")
 */
class ContestEffectInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasDescriptionInterface, EntityHasFlavorTextInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasDescriptionTrait;
    use EntityHasFlavorTextTrait;

    /**
     * @var \App\Entity\ContestEffect
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ContestEffect", inversedBy="children")
     */
    protected $parent;

    /**
     * The base number of hearts the user of this move gets
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $appeal;

    /**
     * The base number of hearts the user’s opponent loses
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $jam;

    /**
     * @return int
     */
    public function getAppeal(): ?int
    {
        return $this->appeal;
    }

    /**
     * @param int $appeal
     *
     * @return self
     */
    public function setAppeal(int $appeal): self
    {
        $this->appeal = $appeal;

        return $this;
    }

    /**
     * @return int
     */
    public function getJam(): ?int
    {
        return $this->jam;
    }

    /**
     * @param int $jam
     *
     * @return self
     */
    public function setJam(int $jam): self
    {
        $this->jam = $jam;

        return $this;
    }
}
