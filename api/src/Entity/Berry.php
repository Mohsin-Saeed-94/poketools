<?php

namespace App\Entity;

use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Time;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Berry, consumable item that grows on trees.
 *
 * @ORM\Entity(repositoryClass="App\Repository\BerryRepository")
 */
class Berry extends AbstractDexEntity
{

    /**
     * @var ItemInVersionGroup
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ItemInVersionGroup", mappedBy="berry")
     */
    protected $item;

    /**
     * @var BerryFirmness
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFirmness")
     * @Assert\NotBlank()
     */
    protected $firmness;

    /**
     * Natural Gift’s power when used with this Berry
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $naturalGiftPower;

    /**
     * The Type that Natural Gift has when used with this Berry
     *
     * @var Type|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     */
    protected $naturalGiftType;

    /**
     * The size of the berry
     *
     * @var Length
     *
     * @ORM\Column(type="safe_object")
     * @Assert\NotBlank()
     */
    protected $size;

    /**
     * The number of berries that can grow on one tree
     *
     * @var Range
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     * @Assert\NotBlank()
     */
    protected $harvest;

    /**
     * Time it takes the tree to grow one stage.
     *
     * Berry trees go through several of these growth stages before they can be
     * picked.
     *
     * @var Time
     *
     * @ORM\Column(type="safe_object")
     * @Assert\NotBlank()
     */
    protected $growthTime;

    /**
     * The speed at which this Berry dries out the soil as it grows. A higher
     * rate means the soil dries more quickly.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $water;

    /**
     * How susceptible this Berry is to weeds.  A higher value means weeding
     * the plant increases the yield more.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $weeds;

    /**
     * How susceptible this Berry is to pests.  A higher value means removing
     * pests near the plant increases the yield more.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $pests;

    /**
     * The smoothness of this Berry, used in making Pokéblocks or Poffins.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $smoothness;

    /**
     * @var Collection|BerryFlavorLevel[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\BerryFlavorLevel", mappedBy="berry", cascade={"all"}, orphanRemoval=true)
     */
    protected $flavors;

    /**
     * Berry constructor.
     */
    public function __construct()
    {
        $this->flavors = new ArrayCollection();
    }

    /**
     * @return ItemInVersionGroup
     */
    public function getItem(): ?ItemInVersionGroup
    {
        return $this->item;
    }

    /**
     * @param ItemInVersionGroup $item
     *
     * @return self
     */
    public function setItem(ItemInVersionGroup $item): self
    {
        $this->item = $item;
        $item->setBerry($this);

        return $this;
    }

    /**
     * @return BerryFirmness
     */
    public function getFirmness(): ?BerryFirmness
    {
        return $this->firmness;
    }

    /**
     * @param BerryFirmness $firmness
     *
     * @return self
     */
    public function setFirmness(BerryFirmness $firmness): self
    {
        $this->firmness = $firmness;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNaturalGiftPower(): ?int
    {
        return $this->naturalGiftPower;
    }

    /**
     * @param int|null $naturalGiftPower
     *
     * @return self
     */
    public function setNaturalGiftPower(?int $naturalGiftPower): self
    {
        $this->naturalGiftPower = $naturalGiftPower;

        return $this;
    }

    /**
     * @return Type|null
     */
    public function getNaturalGiftType(): ?Type
    {
        return $this->naturalGiftType;
    }

    /**
     * @param Type|null $naturalGiftType
     *
     * @return self
     */
    public function setNaturalGiftType(?Type $naturalGiftType): self
    {
        $this->naturalGiftType = $naturalGiftType;

        return $this;
    }

    /**
     * @return Length
     */
    public function getSize(): ?Length
    {
        return $this->size;
    }

    /**
     * @param Length $size
     *
     * @return self
     */
    public function setSize(Length $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return Range
     */
    public function getHarvest(): ?Range
    {
        return $this->harvest;
    }

    /**
     * @param Range $harvest
     *
     * @return self
     */
    public function setHarvest(Range $harvest): self
    {
        $this->harvest = $harvest;

        return $this;
    }

    /**
     * @return Time
     */
    public function getGrowthTime(): ?Time
    {
        return $this->growthTime;
    }

    /**
     * @param Time $growthTime
     *
     * @return self
     */
    public function setGrowthTime(Time $growthTime): self
    {
        $this->growthTime = $growthTime;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWater(): ?int
    {
        return $this->water;
    }

    /**
     * @param int|null $water
     *
     * @return self
     */
    public function setWater(?int $water): self
    {
        $this->water = $water;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeeds(): ?int
    {
        return $this->weeds;
    }

    /**
     * @param int|null $weeds
     *
     * @return self
     */
    public function setWeeds(?int $weeds): self
    {
        $this->weeds = $weeds;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPests(): ?int
    {
        return $this->pests;
    }

    /**
     * @param int|null $pests
     *
     * @return self
     */
    public function setPests(?int $pests): self
    {
        $this->pests = $pests;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSmoothness(): ?int
    {
        return $this->smoothness;
    }

    /**
     * @param int|null $smoothness
     *
     * @return self
     */
    public function setSmoothness(?int $smoothness): self
    {
        $this->smoothness = $smoothness;

        return $this;
    }

    /**
     * @return BerryFlavorLevel[]|Collection
     */
    public function getFlavors()
    {
        return $this->flavors;
    }

    /**
     * @param BerryFlavorLevel $flavorLevel
     *
     * @return self
     */
    public function addFlavor(BerryFlavorLevel $flavorLevel): self
    {
        if (!$this->flavors->contains($flavorLevel)) {
            $this->flavors->add($flavorLevel);
            $flavorLevel->setBerry($this);
        }

        return $this;
    }

    /**
     * @param BerryFlavorLevel $flavorLevel
     *
     * @return self
     */
    public function removeFlavor(BerryFlavorLevel $flavorLevel): self
    {
        if ($this->flavors->contains($flavorLevel)) {
            $this->flavors->removeElement($flavorLevel);
            $flavorLevel->setBerry(null);
        }

        return $this;
    }
}
