<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Contest type, such as "cool" or "smart".
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContestTypeRepository")
 */
class ContestType extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * The corresponding Berry flavor
     *
     * @var BerryFlavor
     *
     * @ORM\OneToOne(targetEntity="App\Entity\BerryFlavor", inversedBy="contestType")
     */
    protected $berryFlavor;

    /**
     * The corresponding Pokéblock color
     *
     * @var PokeblockColor
     *
     * @ORM\OneToOne(targetEntity="App\Entity\PokeblockColor")
     */
    protected $pokeblockColor;

    /**
     * @return BerryFlavor
     */
    public function getBerryFlavor(): ?BerryFlavor
    {
        return $this->berryFlavor;
    }

    /**
     * @param BerryFlavor $berryFlavor
     *
     * @return self
     */
    public function setBerryFlavor(BerryFlavor $berryFlavor): self
    {
        $this->berryFlavor = $berryFlavor;

        return $this;
    }

    /**
     * @return PokeblockColor
     */
    public function getPokeblockColor(): ?PokeblockColor
    {
        return $this->pokeblockColor;
    }

    /**
     * @param PokeblockColor $pokeblockColor
     *
     * @return self
     */
    public function setPokeblockColor(PokeblockColor $pokeblockColor): self
    {
        $this->pokeblockColor = $pokeblockColor;

        return $this;
    }
}
