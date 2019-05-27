<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The efficacy of one type against another
 *
 * @ORM\Entity(repositoryClass="App\Repository\TypeEfficacyRepository")
 */
class TypeEfficacy implements EntityIsSortableInterface
{
    // This allows the type chart to be pre-sorted during data migration and avoid having to
    // join with the types.
    use EntityIsSortableTrait;

    /**
     * @var TypeChart
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TypeChart", inversedBy="efficacies")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $typeChart;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $attackingType;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type")
     * @ORM\Id()
     * @Assert\NotBlank()
     */
    protected $defendingType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min="0", max="200")
     */
    protected $efficacy;

    /**
     * @return TypeChart
     */
    public function getTypeChart(): ?TypeChart
    {
        return $this->typeChart;
    }

    /**
     * @param TypeChart $typeChart
     *
     * @return self
     */
    public function setTypeChart(TypeChart $typeChart): self
    {
        $this->typeChart = $typeChart;

        return $this;
    }

    /**
     * @return Type
     */
    public function getAttackingType(): ?Type
    {
        return $this->attackingType;
    }

    /**
     * @param Type $attackingType
     *
     * @return self
     */
    public function setAttackingType(Type $attackingType): self
    {
        $this->attackingType = $attackingType;

        return $this;
    }

    /**
     * @return Type
     */
    public function getDefendingType(): ?Type
    {
        return $this->defendingType;
    }

    /**
     * @param Type $defendingType
     *
     * @return self
     */
    public function setDefendingType(Type $defendingType): self
    {
        $this->defendingType = $defendingType;

        return $this;
    }

    /**
     * @return int
     */
    public function getEfficacy(): ?int
    {
        return $this->efficacy;
    }

    /**
     * @param int $efficacy
     *
     * @return self
     */
    public function setEfficacy(int $efficacy): self
    {
        $this->efficacy = $efficacy;

        return $this;
    }
}
