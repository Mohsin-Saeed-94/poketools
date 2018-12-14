<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\ItemInVersionGroup;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokémon must be holding this item.
 *
 * @ORM\Entity()
 */
class HeldItemEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var ItemInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup")
     * @Assert\NotBlank()
     *
     * @Groups("read")
     */
    protected $heldItem;

    /**
     * @return ItemInVersionGroup
     */
    public function getHeldItem(): ?ItemInVersionGroup
    {
        return $this->heldItem;
    }

    /**
     * @param ItemInVersionGroup $heldItem
     *
     * @return self
     */
    public function setHeldItem(ItemInVersionGroup $heldItem): self
    {
        $this->heldItem = $heldItem;

        return $this;
    }
}
