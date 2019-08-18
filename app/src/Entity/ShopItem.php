<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shop inventory item
 *
 * @ORM\Entity(repositoryClass="App\Repository\ShopItemRepository")
 */
class ShopItem extends AbstractDexEntity implements EntityIsSortableInterface
{
    use EntityIsSortableTrait;

    /**
     * @var Shop
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Shop", inversedBy="items")
     */
    private $shop;

    /**
     * @var ItemInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup", fetch="EAGER")
     * @Assert\NotNull()
     */
    private $item;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     */
    private $buy;

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

        return $this;
    }

    /**
     * @return int
     */
    public function getBuy(): ?int
    {
        return $this->buy;
    }

    /**
     * @param int $buy
     *
     * @return self
     */
    public function setBuy(int $buy): self
    {
        $this->buy = $buy;

        return $this;
    }

    /**
     * @return Shop
     */
    public function getShop(): Shop
    {
        return $this->shop;
    }

    /**
     * @param Shop $shop
     *
     * @return self
     */
    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }
}
