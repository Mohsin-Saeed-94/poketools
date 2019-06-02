<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A set of rules governing type efficacy.
 *
 * @ORM\Entity(repositoryClass="App\Repository\TypeChartRepository")
 */
class TypeChart extends AbstractDexEntity
{

    /**
     * @var VersionGroup[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\VersionGroup")
     * @Assert\NotBlank()
     */
    protected $versionGroups;

    /**
     * @var TypeEfficacy[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\TypeEfficacy", mappedBy="typeChart", cascade={"ALL"}, fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $efficacies;

    /**
     * TypeChart constructor.
     */
    public function __construct()
    {
        $this->versionGroups = new ArrayCollection();
        $this->efficacies = new ArrayCollection();
    }

    /**
     * @param VersionGroup $versionGroup
     *
     * @return self
     */
    public function addVersionGroup(VersionGroup $versionGroup): self
    {
        if (!$this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->add($versionGroup);
        }

        return $this;
    }

    /**
     * @param VersionGroup $versionGroup
     *
     * @return self
     */
    public function removeVersionGroup(VersionGroup $versionGroup): self
    {
        if ($this->versionGroups->contains($versionGroup)) {
            $this->versionGroups->removeElement($versionGroup);
        }

        return $this;
    }

    /**
     * @return VersionGroup[]|Collection
     */
    public function getVersionGroups()
    {
        return $this->versionGroups;
    }

    /**
     * @param TypeEfficacy $typeEfficacy
     *
     * @return self
     */
    public function addEfficacy(TypeEfficacy $typeEfficacy): self
    {
        if (!$this->efficacies->contains($typeEfficacy)) {
            $this->efficacies->add($typeEfficacy);
            $typeEfficacy->setTypeChart($this);
        }

        return $this;
    }

    /**
     * @param TypeEfficacy $typeEfficacy
     *
     * @return self
     */
    public function removeEfficacy(TypeEfficacy $typeEfficacy): self
    {
        if ($this->efficacies->contains($typeEfficacy)) {
            $this->efficacies->removeElement($typeEfficacy);
            $typeEfficacy->setTypeChart(null);
        }

        return $this;
    }

    /**
     * Sort the internal list of efficacies by attacking type, then defending type.
     *
     * This method should be called after updating effacies before committing
     * changes.
     */
    public function sortEfficacies(): void
    {
        /** @var TypeEfficacy[]|\Traversable|\ArrayIterator $it */
        $it = $this->efficacies->getIterator();
        $it->uasort(
            function (TypeEfficacy $a, TypeEfficacy $b) {
                if ($a->getAttackingType() !== $b->getAttackingType()) {
                    return $a->getAttackingType()->getPosition() - $b->getAttackingType()->getPosition();
                }

                return $a->getDefendingType()->getPosition() - $b->getDefendingType()->getPosition();
            }
        );
        $it->rewind();
        foreach ($it as $k => $typeEfficacy) {
            $typeEfficacy->setPosition($k);
        }

        $this->efficacies = new ArrayCollection(iterator_to_array($it));
    }

    /**
     * @return Type[]|Collection
     */
    public function getTypes(): Collection
    {
        $types = [];
        foreach ($this->getEfficacies() as $efficacy) {
            $types[$efficacy->getAttackingType()
                ->getId()] = $efficacy->getAttackingType();
            $types[$efficacy->getDefendingType()
                ->getId()] = $efficacy->getDefendingType();
        }
        usort(
            $types,
            function (Type $a, Type $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );

        return new ArrayCollection(array_values($types));
    }

    /**
     * @return TypeEfficacy[]|Collection
     */
    public function getEfficacies()
    {
        return $this->efficacies;
    }

    /**
     * @param Type $type
     *
     * @return TypeEfficacy[]|Collection
     */
    public function getEfficaciesForAttackingType(Type $type): Collection
    {
        return $this->efficacies->filter(
            function (TypeEfficacy $efficacy) use ($type) {
                return $efficacy->getAttackingType() === $type;
            }
        );
    }

    /**
     * @param Type $type
     *
     * @return TypeEfficacy[]|Collection
     */
    public function getEfficaciesForDefendingType(Type $type): Collection
    {
        return $this->efficacies->filter(
            function (TypeEfficacy $efficacy) use ($type) {
                return $efficacy->getDefendingType() === $type;
            }
        );
    }

    /**
     * @param Type $attackingType
     * @param Type $defendingType
     *
     * @return TypeEfficacy|null
     */
    public function getEfficacyForMatchup(Type $attackingType, Type $defendingType): ?TypeEfficacy
    {
        foreach ($this->efficacies as $efficacy) {
            if ($efficacy->getAttackingType() === $attackingType && $efficacy->getDefendingType() === $defendingType) {
                return $efficacy;
            }
        }

        return null;
    }
}
