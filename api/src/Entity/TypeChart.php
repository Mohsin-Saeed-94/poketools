<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\OneToMany(targetEntity="App\Entity\TypeEfficacy", mappedBy="typeChart", cascade={"ALL"})
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

        return new ArrayCollection(array_values(array_reverse($types)));
    }

    /**
     * @return TypeEfficacy[]|Collection
     */
    public function getEfficacies()
    {
        return $this->efficacies;
    }
}
