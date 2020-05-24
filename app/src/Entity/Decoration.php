<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A Decoration for the home or secret base
 *
 * @ORM\Entity(repositoryClass="App\Repository\DecorationRepository")
 */
class Decoration extends AbstractDexEntity
{
    /**
     * @var ItemInVersionGroup
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ItemInVersionGroup", mappedBy="decoration")
     */
    protected $item;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $width;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $height;

    /**
     * @return \App\Entity\ItemInVersionGroup
     */
    public function getItem(): ItemInVersionGroup
    {
        return $this->item;
    }

    /**
     * @param \App\Entity\ItemInVersionGroup $item
     * @return Decoration
     */
    public function setItem(ItemInVersionGroup $item): Decoration
    {
        $this->item = $item;
        $item->setDecoration($this);

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Decoration
     */
    public function setWidth(int $width): Decoration
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Decoration
     */
    public function setHeight(int $height): Decoration
    {
        $this->height = $height;

        return $this;
    }
}
