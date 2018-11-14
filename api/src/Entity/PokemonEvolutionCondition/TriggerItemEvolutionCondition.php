<?php


namespace App\Entity\PokemonEvolutionCondition;

use App\Entity\ItemInVersionGroup;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An item must be used on the pokemon (e.g. evolution stone)
 *
 * @ORM\Entity()
 */
class TriggerItemEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var ItemInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemInVersionGroup")
     * @Assert\NotBlank()
     */
    protected $triggerItem;

    /**
     * @return ItemInVersionGroup
     */
    public function getTriggerItem(): ?ItemInVersionGroup
    {
        return $this->triggerItem;
    }

    /**
     * @param ItemInVersionGroup $triggerItem
     *
     * @return self
     */
    public function setTriggerItem(ItemInVersionGroup $triggerItem): self
    {
        $this->triggerItem = $triggerItem;

        return $this;
    }
}
