<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\ItemInVersionGroup;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An item must be in the player's bag.
 *
 * @ORM\Entity()
 */
class ItemInBagEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var ItemInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup")
     * @Assert\NotBlank()
     */
    protected $bagItem;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return sprintf('[]{item:%s} in the bag', $this->getBagItem()->getSlug());
    }

    /**
     * @return ItemInVersionGroup
     */
    public function getBagItem(): ?ItemInVersionGroup
    {
        return $this->bagItem;
    }

    /**
     * @param ItemInVersionGroup $bagItem
     *
     * @return self
     */
    public function setBagItem(ItemInVersionGroup $bagItem): self
    {
        $this->bagItem = $bagItem;

        return $this;
    }
}
