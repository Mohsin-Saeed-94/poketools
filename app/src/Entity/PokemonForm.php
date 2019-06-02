<?php

namespace App\Entity;

use App\Entity\Media\PokemonArt;
use App\Entity\Media\PokemonSprite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An individual form of a Pokémon.
 *
 * This includes every variant (except color differences) of every Pokémon,
 * regardless of how the games treat them. Even Pokémon with no alternate forms
 * have one form to represent their lone “normal” form.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonFormRepository")
 */
class PokemonForm extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface, EntityIsSortableInterface, EntityHasIconInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;
    use EntityIsSortableTrait;
    use EntityHasIconTrait;

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
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonFormPokeathlonStat", mappedBy="pokemonForm", cascade={"ALL"},
     *     orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $pokeathlonStats;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    protected $cry;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url()
     */
    protected $footprint;

    /**
     * @var Collection|PokemonSprite[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Media\PokemonSprite", mappedBy="pokemonForm", cascade={"ALL"},
     *     orphanRemoval=true, fetch="EAGER")
     */
    protected $sprites;

    /**
     * @var Collection|PokemonArt[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Media\PokemonArt", mappedBy="pokemonForm", cascade={"ALL"},
     *     orphanRemoval=true, fetch="EAGER")
     */
    protected $art;

    /**
     * PokemonForm constructor.
     */
    public function __construct()
    {
        $this->pokeathlonStats = new ArrayCollection();
        $this->sprites = new ArrayCollection();
        $this->art = new ArrayCollection();
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
     * @return int
     */
    public function getPokeathlonStatTotal(): int
    {
        $total = 0;

        foreach ($this->getPokeathlonStats() as $pokeathlonStat) {
            $total += $pokeathlonStat->getBaseValue();
        }

        return $total;
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

    /**
     * @return string|null
     */
    public function getCry(): ?string
    {
        return $this->cry;
    }

    /**
     * @param string|null $cry
     *
     * @return self
     */
    public function setCry(?string $cry): self
    {
        $this->cry = $cry;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFootprint(): ?string
    {
        return $this->footprint;
    }

    /**
     * @param string|null $footprint
     *
     * @return self
     */
    public function setFootprint(?string $footprint): self
    {
        $this->footprint = $footprint;

        return $this;
    }

    /**
     * @param PokemonSprite $sprite
     *
     * @return self
     */
    public function addSprite(PokemonSprite $sprite): self
    {
        $matches = $this->filterMatchingSprites($sprite);
        if ($matches->count() === 0) {
            $this->sprites->add($sprite);
            $sprite->setPokemonForm($this);
        }

        return $this;
    }

    /**
     * @param PokemonSprite $sprite
     *
     * @return Collection
     */
    protected function filterMatchingSprites(PokemonSprite $sprite): Collection
    {
        $matches = $this->getSprites()->filter(
            function (PokemonSprite $pokemonSprite) use ($sprite) {
                return $pokemonSprite->getUrl() === $sprite->getUrl();
            }
        );

        return $matches;
    }

    /**
     * @return PokemonSprite[]|Collection
     */
    public function getSprites()
    {
        return $this->sprites;
    }

    /**
     * @return PokemonSprite|null
     */
    public function getDefaultSprite(): ?PokemonSprite
    {
        if ($this->sprites->isEmpty()) {
            return null;
        }

        return $this->sprites->first();
    }

    /**
     * @param PokemonSprite $sprite
     *
     * @return self
     */
    public function removeSprite(PokemonSprite $sprite): self
    {
        $matches = $this->filterMatchingSprites($sprite);
        if ($matches->count() > 0) {
            foreach ($matches as $match) {
                $this->sprites->removeElement($match);
            }
        }

        return $this;
    }

    /**
     * @param PokemonArt $art
     *
     * @return self
     */
    public function addArt(PokemonArt $art): self
    {
        $matches = $this->filterMatchingArt($art);
        if ($matches->count() === 0) {
            $this->art->add($art);
            $art->setPokemonForm($this);
        }

        return $this;
    }

    /**
     * @param PokemonArt $art
     *
     * @return Collection
     */
    protected function filterMatchingArt(PokemonArt $art): Collection
    {
        $matches = $this->getArt()->filter(
            function (PokemonArt $pokemonSprite) use ($art) {
                return $pokemonSprite->getUrl() === $art->getUrl();
            }
        );

        return $matches;
    }

    /**
     * @return Collection|PokemonArt[]
     */
    public function getArt()
    {
        return $this->art;
    }

    /**
     * @param PokemonArt $art
     *
     * @return self
     */
    public function removeArt(PokemonArt $art): self
    {
        $matches = $this->filterMatchingArt($art);
        if ($matches->count() > 0) {
            foreach ($matches as $match) {
                $this->art->removeElement($match);
            }
        }

        return $this;
    }
}
