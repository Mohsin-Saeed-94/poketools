<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\PokemonEvolutionCondition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The value of attack - defense must match this.
 *
 * @ORM\Entity()
 */
class PhysicalStatsDifferenceEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Assert\NotNull()
     *
     * @Groups("read")
     */
    protected $physicalStatsDifference;

    /**
     * @return int
     */
    public function getPhysicalStatsDifference(): ?int
    {
        return $this->physicalStatsDifference;
    }

    /**
     * @param int $physicalStatsDifference
     *
     * @return self
     */
    public function setPhysicalStatsDifference(int $physicalStatsDifference): self
    {
        if ($physicalStatsDifference < 0) {
            $this->physicalStatsDifference = -1;
        } elseif ($physicalStatsDifference > 0) {
            $this->physicalStatsDifference = 1;
        } else {
            $this->physicalStatsDifference = 0;
        }

        return $this;
    }
}
