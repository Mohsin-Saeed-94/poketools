<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An individual form of a Pokémon.
 *
 * This includes every variant (except color differences) of every Pokémon,
 * regardless of how the games treat them. Even Pokémon with no alternate forms
 * have one form to represent their lone “normal” form.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonFormRepository")
 */
class PokemonForm extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;
    use EntityIsSortableTrait;

    /**
     * URL slug
     *
     * @var string|null
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(fields={"name"}, handlers={@Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\RelativeSlugHandler", options={
     *     @Gedmo\SlugHandlerOption(name="relationField", value="pokemon"),
     *     @Gedmo\SlugHandlerOption(name="relationSlugField", value="slug")
     * })})
     */
    protected $slug;

    /**
     * @var Pokemon
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="forms")
     */
    protected $pokemon;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $formName;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $battleOnly = false;

    /**
     * @var Collection|PokemonFormPokeathlonStat[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonFormPokeathlonStat", mappedBy="pokemonForm", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $pokeathlonStats;

    /**
     * PokemonForm constructor.
     */
    public function __construct()
    {
        $this->pokeathlonStats = new ArrayCollection();
    }

    /**
     * @return Pokemon
     */
    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return self
     */
    public function setPokemon(Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormName(): ?string
    {
        return $this->formName;
    }

    /**
     * @param string $formName
     *
     * @return self
     */
    public function setFormName(string $formName): self
    {
        $this->formName = $formName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBattleOnly(): ?bool
    {
        return $this->battleOnly;
    }

    /**
     * @param bool $battleOnly
     *
     * @return self
     */
    public function setBattleOnly(bool $battleOnly): self
    {
        $this->battleOnly = $battleOnly;

        return $this;
    }

    /**
     * @param PokeathlonStat $pokeathlonStat
     *
     * @return PokemonFormPokeathlonStat|null
     */
    public function getPokeathlonStatData(PokeathlonStat $pokeathlonStat): ?PokemonFormPokeathlonStat
    {
        foreach ($this->getPokeathlonStats() as $checkPokeathlonStat) {
            if ($checkPokeathlonStat->getPokeathlonStat() === $pokeathlonStat) {
                return $checkPokeathlonStat;
            }
        }

        return null;
    }

    /**
     * @return PokemonFormPokeathlonStat[]
     */
    public function getPokeathlonStats()
    {
        return $this->pokeathlonStats;
    }

    /**
     * @param PokemonFormPokeathlonStat $pokeathlonStat
     *
     * @return self
     */
    public function addPokeathlonStat(PokemonFormPokeathlonStat $pokeathlonStat): self
    {
        if (!$this->pokeathlonStats->contains($pokeathlonStat)) {
            $this->pokeathlonStats->add($pokeathlonStat);
            $pokeathlonStat->setPokemonForm($this);
        }

        return $this;
    }

    /**
     * @param PokemonFormPokeathlonStat $pokeathlonStat
     *
     * @return self
     */
    public function removePokeathlonStat(PokemonFormPokeathlonStat $pokeathlonStat): self
    {
        if ($this->pokeathlonStats->contains($pokeathlonStat)) {
            $this->pokeathlonStats->removeElement($pokeathlonStat);
        }

        return $this;
    }
}
