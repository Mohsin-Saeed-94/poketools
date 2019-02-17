<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Item from the games, like “Poké Ball” or “Bicycle”.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemInVersionGroupRepository")
 *
 * @method Item getParent()
 * @method self setParent(Item $parent)
 */
class ItemInVersionGroup extends AbstractDexEntity implements
    EntityHasNameInterface,
    EntityHasSlugInterface,
    EntityGroupedByVersionGroupInterface,
    EntityHasFlavorTextInterface,
    EntityHasDescriptionInterface,
    EntityHasParentInterface,
    EntityHasIconInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasFlavorTextTrait;
    use EntityHasDescriptionTrait;
    use EntityHasParentTrait;
    use EntityHasIconTrait;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="children")
     */
    protected $parent;

    /**
     * @var ItemCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemCategory", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $category;

    /**
     * @var ItemPocketInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemPocketInVersionGroup", fetch="EAGER")
     */
    protected $pocket;

    /**
     * Cost of the item when bought.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $buy;

    /**
     * Cost of the item when sold.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $sell;

    /**
     * Effect of the move Fling when used with this item.
     *
     * @var ItemFlingEffect|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemFlingEffect", fetch="EAGER")
     */
    protected $flingEffect;

    /**
     * Power of the move Fling when used with this item.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $flingPower;

    /**
     * Item attributes
     *
     * @var Collection|ItemFlag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ItemFlag", fetch="EAGER")
     */
    protected $flags;

    /**
     * @var Berry|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Berry", inversedBy="item", cascade={"all"}, orphanRemoval=true, fetch="EAGER")
     */
    protected $berry;

    /**
     * @var Machine|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Machine", inversedBy="item", cascade={"all"}, orphanRemoval=true, fetch="EAGER")
     */
    protected $machine;

    /**
     * ItemInVersionGroup constructor.
     */
    public function __construct()
    {
        $this->flags = new ArrayCollection();
    }

    /**
     * @return ItemCategory
     */
    public function getCategory(): ?ItemCategory
    {
        return $this->category;
    }

    /**
     * @param ItemCategory $category
     *
     * @return self
     */
    public function setCategory(ItemCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return ItemPocketInVersionGroup
     */
    public function getPocket(): ?ItemPocketInVersionGroup
    {
        return $this->pocket;
    }

    /**
     * @param ItemPocketInVersionGroup $pocket
     *
     * @return self
     */
    public function setPocket(ItemPocketInVersionGroup $pocket): self
    {
        $this->pocket = $pocket;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBuy(): ?int
    {
        return $this->buy;
    }

    /**
     * @param int|null $buy
     *
     * @return self
     */
    public function setBuy(?int $buy): self
    {
        $this->buy = $buy;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSell(): ?int
    {
        return $this->sell;
    }

    /**
     * @param int|null $sell
     *
     * @return self
     */
    public function setSell(?int $sell): self
    {
        $this->sell = $sell;

        return $this;
    }

    /**
     * @return ItemFlingEffect|null
     */
    public function getFlingEffect(): ?ItemFlingEffect
    {
        return $this->flingEffect;
    }

    /**
     * @param ItemFlingEffect|null $flingEffect
     *
     * @return self
     */
    public function setFlingEffect(?ItemFlingEffect $flingEffect): self
    {
        $this->flingEffect = $flingEffect;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFlingPower(): ?int
    {
        return $this->flingPower;
    }

    /**
     * @param int|null $flingPower
     *
     * @return self
     */
    public function setFlingPower(?int $flingPower): self
    {
        $this->flingPower = $flingPower;

        return $this;
    }

    /**
     * @return ItemFlag[]|Collection
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param ItemFlag $flag
     *
     * @return self
     */
    public function addFlag(ItemFlag $flag): self
    {
        if (!$this->flags->contains($flag)) {
            $this->flags->add($flag);
        }

        return $this;
    }

    /**
     * @param ItemFlag $flag
     *
     * @return self
     */
    public function removeFlag(ItemFlag $flag): self
    {
        if ($this->flags->contains($flag)) {
            $this->flags->removeElement($flag);
        }

        return $this;
    }

    /**
     * @return Berry
     */
    public function getBerry(): ?Berry
    {
        return $this->berry;
    }

    /**
     * @param Berry $berry
     *
     * @return self
     */
    public function setBerry(?Berry $berry): self
    {
        $this->berry = $berry;

        return $this;
    }

    /**
     * @return Machine
     */
    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    /**
     * @param Machine $machine
     *
     * @return self
     */
    public function setMachine(?Machine $machine): self
    {
        $this->machine = $machine;

        return $this;
    }
}
