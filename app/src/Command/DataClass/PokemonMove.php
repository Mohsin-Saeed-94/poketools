<?php


namespace App\Command\DataClass;


final class PokemonMove
{
    /**
     * @var string
     */
    private $species;

    /**
     * @var string
     */
    private $pokemon;

    /**
     * @var string
     */
    private $versionGroup;

    /**
     * @var string
     */
    private $move;

    /**
     * @var string
     */
    private $learnMethod;

    /**
     * @var int|null
     */
    private $level;

    /**
     * @var string|null
     */
    private $machine;

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
     * @return PokemonMove
     */
    public function setSpecies(string $species): PokemonMove
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
     * @return PokemonMove
     */
    public function setPokemon(string $pokemon): PokemonMove
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersionGroup(): string
    {
        return $this->versionGroup;
    }

    /**
     * @param string $versionGroup
     *
     * @return PokemonMove
     */
    public function setVersionGroup(string $versionGroup): PokemonMove
    {
        $this->versionGroup = $versionGroup;

        return $this;
    }

    /**
     * @return string
     */
    public function getMove(): string
    {
        return $this->move;
    }

    /**
     * @param string $move
     *
     * @return PokemonMove
     */
    public function setMove(string $move): PokemonMove
    {
        $this->move = $move;

        return $this;
    }

    /**
     * @return string
     */
    public function getLearnMethod(): string
    {
        return $this->learnMethod;
    }

    /**
     * @param string $learnMethod
     *
     * @return PokemonMove
     */
    public function setLearnMethod(string $learnMethod): PokemonMove
    {
        $this->learnMethod = $learnMethod;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int|null $level
     *
     * @return PokemonMove
     */
    public function setLevel($level): PokemonMove
    {
        if ($level === '') {
            $this->level = null;
        } else {
            $this->level = $level;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMachine(): ?string
    {
        return $this->machine;
    }

    /**
     * @param string|null $machine
     *
     * @return PokemonMove
     */
    public function setMachine(?string $machine): PokemonMove
    {
        if ($machine === '') {
            $this->machine = null;
        } else {
            $this->machine = $machine;
        }

        return $this;
    }
}
