<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A TM or HM; numbered item that can teach a move to a Pokémon.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MachineRepository")
 */
class Machine extends AbstractDexEntity
{

    public const MACHINE_TM = 'TM';

    public const MACHINE_HM = 'HM';

    /**
     * @var ItemInVersionGroup
     *
     * @ORM\OneToOne(targetEntity="App\Entity\ItemInVersionGroup", mappedBy="machine")
     */
    protected $item;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\Choice(callback="validMachineTypes")
     */
    protected $type;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(0)
     */
    protected $number;

    /**
     * @var MoveInVersionGroup
     *
     * @ORM\OneToOne(targetEntity="App\Entity\MoveInVersionGroup", inversedBy="machine")
     * @Assert\NotBlank()
     */
    protected $move;

    /**
     * Get a list of valid machine types for use with validation.
     *
     * @internal
     *
     * @return array
     */
    public static function validMachineTypes(): array
    {
        return [self::MACHINE_TM, self::MACHINE_HM];
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
        $item->setMachine($this);

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return self
     */
    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return MoveInVersionGroup
     */
    public function getMove(): ?MoveInVersionGroup
    {
        return $this->move;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function setMove(MoveInVersionGroup $move): self
    {
        $this->move = $move;

        return $this;
    }
}
