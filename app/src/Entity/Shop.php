<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Item shop (e.g. Poke Mart)
 *
 * @ORM\Entity(repositoryClass="App\Repository\ShopRepository")
 */
class Shop extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface
{
    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;

    /**
     * @var LocationArea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationArea", inversedBy="shops")
     */
    private $locationArea;

    /**
     * @var Collection|ShopItem[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ShopItem", mappedBy="shop", fetch="EAGER")
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $items;

    /**
     * Shop constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return LocationArea
     */
    public function getLocationArea(): LocationArea
    {
        return $this->locationArea;
    }

    /**
     * @param LocationArea $locationArea
     *
     * @return Shop
     */
    public function setLocationArea(LocationArea $locationArea): Shop
    {
        $this->locationArea = $locationArea;

        return $this;
    }

    /**
     * @return ShopItem[]|Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ShopItem $shopItem
     *
     * @return self
     */
    public function addItem(ShopItem $shopItem): self
    {
        if (!$this->items->contains($shopItem)) {
            $this->items->add($shopItem);
            $shopItem->setShop($this);
        }

        return $this;
    }

    /**
     * @param ShopItem $shopItem
     *
     * @return Shop
     */
    public function removeItem(ShopItem $shopItem): self
    {
        if ($this->items->contains($shopItem)) {
            $this->items->removeElement($shopItem);
            $shopItem->setShop(null);
        }
    }
}
