<?php
/**
 * @file Encounter.php
 */

namespace App\Command\DataClass;


use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;

final class Encounter
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $species;

    /**
     * @var string
     */
    private $pokemon;

    /**
     * @var Range
     */
    private $level;

    /**
     * @var int|null
     */
    private $chance;

    /**
     * @var ArrayCollection|string[]
     */
    private $conditions;

    /**
     * @var string|null
     */
    private $note;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     *
     * @return self
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * @param string $area
     *
     * @return self
     */
    public function setArea(string $area): self
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpecies(): string
    {
        return $this->species;
    }

    /**
     * @param string $species
     *
     * @return self
     */
    public function setSpecies(string $species): self
    {
        $this->species = $species;

        return $this;
    }

    /**
     * @return string
     */
    public function getPokemon(): string
    {
        return $this->pokemon;
    }

    /**
     * @param string $pokemon
     *
     * @return self
     */
    public function setPokemon(string $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * @return Range
     */
    public function getLevel(): Range
    {
        return $this->level;
    }

    /**
     * @param Range $level
     *
     * @return self
     */
    public function setLevel(Range $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getChance(): ?int
    {
        return $this->chance;
    }

    /**
     * @param int|null $chance
     *
     * @return self
     */
    public function setChance($chance): self
    {
        if (empty($chance)) {
            $chance = null;
        }
        $this->chance = $chance;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getConditions(): ArrayCollection
    {
        return $this->conditions;
    }

    /**
     * @param array|ArrayCollection|string $conditions
     *
     * @return self
     */
    public function setConditions($conditions): self
    {
        if (!is_iterable($conditions)) {
            $conditions = [$conditions];
        }
        if (!is_a($conditions, ArrayCollection::class)) {
            $conditions = new ArrayCollection($conditions);
        }
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @param string $condition
     *
     * @return self
     */
    public function addCondition(string $condition): self
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     *
     * @return self
     */
    public function setNote(?string $note): self
    {
        if (empty($note)) {
            $note = null;
        }

        $this->note = $note;

        return $this;
    }
}
