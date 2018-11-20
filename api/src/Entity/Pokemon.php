<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Pokémon are defined as a form with different types, moves, or other game-
 * changing properties.
 *
 * e.g. There are four separate "Pokemon" for Deoxys, but only one for Unown.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PokemonRepository")
 *
 * @Gedmo\Tree(type="materializedPath")
 */
class Pokemon extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface, EntityHasDefaultInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;
    use EntityHasDefaultTrait;

    /**
     * URL slug
     *
     * @var string|null
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @Gedmo\Slug(fields={"name"}, handlers={@Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\RelativeSlugHandler", options={
     *     @Gedmo\SlugHandlerOption(name="relationField", value="species"),
     *     @Gedmo\SlugHandlerOption(name="relationSlugField", value="slug")
     * })})
     */
    protected $slug;

    /**
     * Unique Id
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Gedmo\TreePathSource()
     */
    protected $id;

    /**
     * @var PokemonSpeciesInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonSpeciesInVersionGroup", inversedBy="pokemon")
     */
    protected $species;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    protected $evolutionPath;

    /**
     * The Pokémon from which this one evolves
     *
     * @var Pokemon|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon", inversedBy="evolutionChildren")
     * @Gedmo\TreeParent()
     */
    protected $evolutionParent;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeLevel()
     */
    protected $evolutionStage;

    /**
     * @var Collection|Pokemon[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="evolutionParent")
     */
    protected $evolutionChildren;

    /**
     * @var Collection|PokemonEvolutionCondition[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonEvolutionCondition", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $evolutionConditions;

    /**
     * This Pokémon’s Pokédex color, as used for a search function in the games.
     *
     * @var PokemonColor|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonColor", fetch="EAGER")
     */
    protected $color;

    /**
     * This Pokémon’s body shape, as used for a search function in the games.
     *
     * @var PokemonShapeInVersionGroup|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonShapeInVersionGroup", fetch="EAGER")
     */
    protected $shape;

    /**
     * This Pokémon’s habitat, as used for a search function in the games.
     *
     * @var PokemonHabitat|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PokemonHabitat", fetch="EAGER")
     */
    protected $habitat;

    /**
     * The chance of this Pokémon being female; null if genderless.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     */
    protected $femaleRate;

    /**
     * The base capture rate
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Assert\Range(min="0", max="255")
     */
    protected $captureRate;

    /**
     * The tameness when caught by a normal ball
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="255")
     */
    protected $happiness;

    /**
     * True if the Pokémon is a baby.
     *
     * A baby is a lowest-stage Pokémon that cannot breed but whose evolved form
     * can.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $baby = false;

    /**
     * Initial hatch counter
     *
     * The exact formula varies by game, however this number of steps will
     * always be a part of that formula.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $hatchSteps;

    /**
     * The growth rate for this Pokémon
     *
     * @var GrowthRate
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\GrowthRate", fetch="EAGER")
     */
    protected $growthRate;

    /**
     * True if a particular individual of this species can switch between its
     * different forms at will
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $formsSwitchable = false;

    /**
     * The short flavor text, such as “Seed” or “Lizard”; usually affixed with
     * the word “Pokémon”
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $genus;

    /**
     * Description of how the forms work
     *
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $formsNote;

    /**
     * @var Collection|PokemonFlavorText[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonFlavorText", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $flavorText;

    /**
     * @var Collection|EggGroup[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\EggGroup", fetch="EAGER")
     */
    protected $eggGroups;

    /**
     * @var PokemonPalParkData
     *
     * @ORM\OneToOne(targetEntity="App\Entity\PokemonPalParkData", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     */
    protected $palParkData;

    /**
     * @var Length
     *
     * @ORM\Column(type="safe_object")
     */
    protected $height;

    /**
     * @var Mass
     *
     * @ORM\Column(type="safe_object")
     */
    protected $weight;

    /**
     * The base EXP gained when defeating this Pokémon
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $experience;

    /**
     * @var Collection|PokemonAbility[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonAbility", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $abilities;

    /**
     * @var Collection|PokemonWildHeldItem[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonWildHeldItem", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     */
    protected $wildHeldItems;

    /**
     * @var Collection|PokemonMove[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonMove", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     */
    protected $moves;

    /**
     * @var Collection|PokemonStat[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonStat", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $stats;

    /**
     * @var Collection|PokemonType[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonType", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $types;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $mega = false;

    /**
     * @var Collection|PokemonForm[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PokemonForm", mappedBy="pokemon", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     * @ORM\OrderBy({"isDefault" = "DESC", "position" = "ASC"})
     */
    protected $forms;

    /**
     * Pokemon constructor.
     */
    public function __construct()
    {
        $this->evolutionChildren = new ArrayCollection();
        $this->evolutionConditions = new ArrayCollection();
        $this->flavorText = new ArrayCollection();
        $this->eggGroups = new ArrayCollection();
        $this->abilities = new ArrayCollection();
        $this->wildHeldItems = new ArrayCollection();
        $this->moves = new ArrayCollection();
        $this->stats = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->forms = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getEvolutionPath(): ?string
    {
        return $this->evolutionPath;
    }

    /**
     * @param string|null $evolutionPath
     *
     * @return self
     */
    public function setEvolutionPath(?string $evolutionPath): self
    {
        $this->evolutionPath = $evolutionPath;

        return $this;
    }

    /**
     * @return Pokemon|null
     */
    public function getEvolutionParent(): ?Pokemon
    {
        return $this->evolutionParent;
    }

    /**
     * @param Pokemon|null $evolutionParent
     *
     * @return self
     */
    public function setEvolutionParent(?Pokemon $evolutionParent): self
    {
        $this->evolutionParent = $evolutionParent;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEvolutionStage(): ?int
    {
        return $this->evolutionStage;
    }

    /**
     * @return Pokemon[]|Collection
     */
    public function getEvolutionChildren()
    {
        return $this->evolutionChildren;
    }

    /**
     * @return PokemonEvolutionCondition[]|Collection
     */
    public function getEvolutionConditions()
    {
        return $this->evolutionConditions;
    }

    /**
     * @param PokemonEvolutionCondition $evolutionCondition
     *
     * @return self
     */
    public function addEvolutionCondition(PokemonEvolutionCondition $evolutionCondition): self
    {
        if (!$this->evolutionConditions->contains($evolutionCondition)) {
            $this->evolutionConditions->add($evolutionCondition);
            $evolutionCondition->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonEvolutionCondition $evolutionCondition
     *
     * @return self
     */
    public function removeEvolutionCondition(PokemonEvolutionCondition $evolutionCondition): self
    {
        if ($this->evolutionConditions->contains($evolutionCondition)) {
            $this->evolutionConditions->removeElement($evolutionCondition);
        }

        return $this;
    }

    /**
     * @return PokemonColor|null
     */
    public function getColor(): ?PokemonColor
    {
        return $this->color;
    }

    /**
     * @param PokemonColor|null $color
     *
     * @return self
     */
    public function setColor(?PokemonColor $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return PokemonShapeInVersionGroup|null
     */
    public function getShape(): ?PokemonShapeInVersionGroup
    {
        return $this->shape;
    }

    /**
     * @param PokemonShapeInVersionGroup|null $shape
     *
     * @return self
     */
    public function setShape(?PokemonShapeInVersionGroup $shape): self
    {
        $this->shape = $shape;

        return $this;
    }

    /**
     * @return PokemonHabitat|null
     */
    public function getHabitat(): ?PokemonHabitat
    {
        return $this->habitat;
    }

    /**
     * @param PokemonHabitat|null $habitat
     *
     * @return self
     */
    public function setHabitat(?PokemonHabitat $habitat): self
    {
        $this->habitat = $habitat;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFemaleRate(): ?int
    {
        return $this->femaleRate;
    }

    /**
     * @param int|null $femaleRate
     *
     * @return self
     */
    public function setFemaleRate(?int $femaleRate): self
    {
        $this->femaleRate = $femaleRate;

        return $this;
    }

    /**
     * @return int
     */
    public function getCaptureRate(): ?int
    {
        return $this->captureRate;
    }

    /**
     * @param int $captureRate
     *
     * @return self
     */
    public function setCaptureRate(int $captureRate): self
    {
        $this->captureRate = $captureRate;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHappiness(): ?int
    {
        return $this->happiness;
    }

    /**
     * @param int|null $happiness
     *
     * @return self
     */
    public function setHappiness(?int $happiness): self
    {
        $this->happiness = $happiness;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBaby(): bool
    {
        return $this->baby;
    }

    /**
     * @param bool $baby
     *
     * @return self
     */
    public function setBaby(bool $baby): self
    {
        $this->baby = $baby;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHatchSteps(): ?int
    {
        return $this->hatchSteps;
    }

    /**
     * @param int|null $hatchSteps
     *
     * @return self
     */
    public function setHatchSteps(?int $hatchSteps): self
    {
        $this->hatchSteps = $hatchSteps;

        return $this;
    }

    /**
     * @return GrowthRate
     */
    public function getGrowthRate(): ?GrowthRate
    {
        return $this->growthRate;
    }

    /**
     * @param GrowthRate $growthRate
     *
     * @return self
     */
    public function setGrowthRate(GrowthRate $growthRate): self
    {
        $this->growthRate = $growthRate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFormsSwitchable(): bool
    {
        return $this->formsSwitchable;
    }

    /**
     * @param bool $formsSwitchable
     *
     * @return self
     */
    public function setFormsSwitchable(bool $formsSwitchable): self
    {
        $this->formsSwitchable = $formsSwitchable;

        return $this;
    }

    /**
     * @return string
     */
    public function getGenus(): ?string
    {
        return $this->genus;
    }

    /**
     * @param string $genus
     *
     * @return self
     */
    public function setGenus(string $genus): self
    {
        $this->genus = $genus;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormsNote(): ?string
    {
        return $this->formsNote;
    }

    /**
     * @param string|null $formsNote
     *
     * @return self
     */
    public function setFormsNote(?string $formsNote): self
    {
        $this->formsNote = $formsNote;

        return $this;
    }

    /**
     * @param Version $version
     *
     * @return PokemonFlavorText
     */
    public function getFlavorTextInVersion(Version $version): ?PokemonFlavorText
    {
        foreach ($this->getFlavorText() as $flavorText) {
            if ($flavorText->getVersion() === $version) {
                return $flavorText;
            }
        }

        return null;
    }

    /**
     * @return PokemonFlavorText[]|Collection
     */
    public function getFlavorText()
    {
        return $this->flavorText;
    }

    /**
     * @param PokemonFlavorText $flavorText
     *
     * @return self
     */
    public function addFlavorText(PokemonFlavorText $flavorText): self
    {
        if (!$this->flavorText->contains($flavorText)) {
            $this->flavorText->add($flavorText);
            $flavorText->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonFlavorText $flavorText
     *
     * @return self
     */
    public function removeFlavorText(PokemonFlavorText $flavorText): self
    {
        if ($this->flavorText->contains($flavorText)) {
            $this->flavorText->removeElement($flavorText);
        }

        return $this;
    }

    /**
     * @return EggGroup[]|Collection
     */
    public function getEggGroups()
    {
        return $this->eggGroups;
    }

    /**
     * @param EggGroup $eggGroup
     *
     * @return self
     */
    public function addEggGroup(EggGroup $eggGroup): self
    {
        if (!$this->eggGroups->contains($eggGroup)) {
            $this->eggGroups->add($eggGroup);
        }

        return $this;
    }

    /**
     * @param EggGroup $eggGroup
     *
     * @return self
     */
    public function removeEggGroup(EggGroup $eggGroup): self
    {
        if ($this->eggGroups->contains($eggGroup)) {
            $this->eggGroups->removeElement($eggGroup);
        }

        return $this;
    }

    /**
     * @return PokemonPalParkData
     */
    public function getPalParkData(): ?PokemonPalParkData
    {
        return $this->palParkData;
    }

    /**
     * @param PokemonPalParkData $palParkData
     *
     * @return self
     */
    public function setPalParkData(PokemonPalParkData $palParkData): self
    {
        $this->palParkData = $palParkData;

        return $this;
    }

    /**
     * @return Length
     */
    public function getHeight(): ?Length
    {
        return $this->height;
    }

    /**
     * @param Length $height
     *
     * @return self
     */
    public function setHeight(Length $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return Mass
     */
    public function getWeight(): ?Mass
    {
        return $this->weight;
    }

    /**
     * @param Mass $weight
     *
     * @return self
     */
    public function setWeight(Mass $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return int
     */
    public function getExperience(): ?int
    {
        return $this->experience;
    }

    /**
     * @param int $experience
     *
     * @return self
     */
    public function setExperience(int $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * @param AbilityInVersionGroup $ability
     *
     * @return PokemonAbility
     */
    public function getAbilityData(AbilityInVersionGroup $ability): ?PokemonAbility
    {
        foreach ($this->getAbilities() as $pokemonAbility) {
            if ($pokemonAbility->getAbility() === $ability) {
                return $pokemonAbility;
            }
        }

        return null;
    }

    /**
     * @return PokemonAbility[]|Collection
     */
    public function getAbilities()
    {
        return $this->abilities;
    }

    /**
     * @return PokemonSpeciesInVersionGroup
     */
    public function getSpecies(): ?PokemonSpeciesInVersionGroup
    {
        return $this->species;
    }

    /**
     * @param PokemonSpeciesInVersionGroup $species
     *
     * @return self
     */
    public function setSpecies(PokemonSpeciesInVersionGroup $species): self
    {
        $this->species = $species;

        return $this;
    }

    /**
     * @param PokemonAbility $ability
     *
     * @return self
     */
    public function addAbility(PokemonAbility $ability): self
    {
        if (!$this->abilities->contains($ability)) {
            $this->abilities->add($ability);
            $ability->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonAbility $ability
     *
     * @return self
     */
    public function removeAbility(PokemonAbility $ability): self
    {
        if ($this->abilities->contains($ability)) {
            $this->abilities->removeElement($ability);
        }

        return $this;
    }

    /**
     * @param Version $version
     *
     * @return Collection
     */
    public function getWildHeldItemsInVersion(Version $version): Collection
    {
        return $this->getWildHeldItems()->filter(
            function (PokemonWildHeldItem $wildHeldItem) use ($version) {
                return ($wildHeldItem->getVersion() === $version);
            }
        );
    }

    /**
     * @return PokemonWildHeldItem[]|Collection
     */
    public function getWildHeldItems()
    {
        return $this->wildHeldItems;
    }

    /**
     * @param PokemonWildHeldItem $heldItem
     *
     * @return self
     */
    public function addWildHeldItem(PokemonWildHeldItem $heldItem): self
    {
        if (!$this->wildHeldItems->contains($heldItem)) {
            $this->wildHeldItems->add($heldItem);
            $heldItem->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonWildHeldItem $heldItem
     *
     * @return self
     */
    public function removeWildHeldItem(PokemonWildHeldItem $heldItem): self
    {
        if ($this->wildHeldItems->contains($heldItem)) {
            $this->wildHeldItems->removeElement($heldItem);
        }

        return $this;
    }

    /**
     * @return PokemonMove[]|Collection
     */
    public function getMoves()
    {
        return $this->moves;
    }

    /**
     * @param PokemonMove $move
     *
     * @return self
     */
    public function addMove(PokemonMove $move): self
    {
        if (!$this->moves->contains($move)) {
            $this->moves->add($move);
            $move->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonMove $move
     *
     * @return self
     */
    public function removeMove(PokemonMove $move): self
    {
        if ($this->moves->contains($move)) {
            $this->moves->removeElement($move);
        }

        return $this;
    }

    /**
     * @param Stat $stat
     *
     * @return PokemonStat
     */
    public function getStatData(Stat $stat): ?PokemonStat
    {
        foreach ($this->getStats() as $pokemonStat) {
            if ($pokemonStat->getStat() === $stat) {
                return $pokemonStat;
            }
        }

        return null;
    }

    /**
     * @return PokemonStat[]|Collection
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param PokemonStat $stat
     *
     * @return self
     */
    public function addStat(PokemonStat $stat): self
    {
        if (!$this->stats->contains($stat)) {
            $this->stats->add($stat);
            $stat->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonStat $stat
     *
     * @return self
     */
    public function removeStat(PokemonStat $stat): self
    {
        if ($this->stats->contains($stat)) {
            $this->stats->removeElement($stat);
        }

        return $this;
    }

    /**
     * @return PokemonType[]|Collection
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param PokemonType $type
     *
     * @return self
     */
    public function addType(PokemonType $type): self
    {
        if (!$this->types->contains($type)) {
            $this->types->add($type);
            $type->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonType $type
     *
     * @return self
     */
    public function removeType(PokemonType $type): self
    {
        if ($this->types->contains($type)) {
            $this->types->removeElement($type);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isMega(): bool
    {
        return $this->mega;
    }

    /**
     * @param bool $mega
     *
     * @return self
     */
    public function setMega(bool $mega): self
    {
        $this->mega = $mega;

        return $this;
    }

    /**
     * @return PokemonForm[]|Collection
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * @param PokemonForm $form
     *
     * @return self
     */
    public function addForm(PokemonForm $form): self
    {
        if (!$this->forms->contains($form)) {
            $this->forms->add($form);
            $form->setPokemon($this);
        }

        return $this;
    }

    /**
     * @param PokemonForm $form
     *
     * @return self
     */
    public function removeForm(PokemonForm $form): self
    {
        if ($this->forms->contains($form)) {
            $this->forms->removeElement($form);
        }

        return $this;
    }
}
