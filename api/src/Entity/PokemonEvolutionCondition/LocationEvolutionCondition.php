<?php


namespace App\Entity\PokemonEvolutionCondition;


use App\Entity\LocationInVersionGroup;
use App\Entity\PokemonEvolutionCondition;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Pokémon must be in one of these locations
 *
 * @ORM\Entity()
 */
class LocationEvolutionCondition extends PokemonEvolutionCondition
{

    /**
     * @var Collection|LocationInVersionGroup[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\LocationInVersionGroup")
     * @Assert\NotBlank()
     *
     * @Groups("read")
     */
    protected $locations;

    /**
     * LocationEvolutionCondition constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->locations = new ArrayCollection();
    }

    /**
     * @return LocationInVersionGroup[]|Collection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @param LocationInVersionGroup $location
     *
     * @return LocationEvolutionCondition
     */
    public function addLocation(LocationInVersionGroup $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
        }

        return $this;
    }

    /**
     * @param LocationInVersionGroup $location
     *
     * @return LocationEvolutionCondition
     */
    public function removeLocation(LocationInVersionGroup $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
        }

        return $this;
    }
}
