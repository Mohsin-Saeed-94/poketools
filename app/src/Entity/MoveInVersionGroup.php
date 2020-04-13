<?php

namespace App\Entity;

use App\Entity\Embeddable\Range;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A technique or attack a Pokémon can learn to use.
 *
 * @ORM\Entity(repositoryClass="App\Repository\MoveInVersionGroupRepository")
 *
 * @method Move getParent()
 * @method self setParent(Move $parent)
 */
class MoveInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityHasDescriptionInterface, EntityHasFlavorTextInterface
{

    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityHasDescriptionTrait;
    use EntityHasFlavorTextTrait;

    /**
     * @var Move
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Move", inversedBy="children")
     */
    protected $parent;

    /**
     * The move’s elemental type
     *
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $type;

    /**
     * Base power of the move, null if it does not have a set base power.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $power;

    /**
     * Base PP (Power Points) of the move, null if not applicable (e.g. Struggle
     * and Shadow moves).
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $pp;

    /**
     * Accuracy of the move; NULL means it never misses.
     *
     * There is an important distinction between 100% and NULL accuracy - 100%
     * accuracy is still affected by other accuracy reductions.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     */
    protected $accuracy;

    /**
     * The move’s priority bracket
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $priority;

    /**
     * The target (range) of the move
     *
     * @var MoveTarget
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveTarget", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $target;

    /**
     * The damage class (physical/special) of the move.
     *
     * Before Generation 4, this is taken from the move's type.
     *
     * @var MoveDamageClass|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveDamageClass", fetch="EAGER")
     */
    protected $damageClass;

    /**
     * The move’s effect
     *
     * @var MoveEffectInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveEffectInVersionGroup", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $effect;

    /**
     * The chance for a secondary effect. What this is a chance of is specified
     * by the move’s effect.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $effectChance;

    /**
     * The Move’s Contest type (e.g. cool or smart), if applicable in this
     * version group.
     *
     * @var ContestType|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ContestType", fetch="EAGER")
     */
    protected $contestType;

    /**
     * The move’s Contest effect, if applicable in this version group.
     *
     * @var ContestEffect|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ContestEffect", fetch="EAGER")
     */
    protected $contestEffect;

    /**
     * The move’s Super Contest effect, if applicable in this version group.
     *
     * @var SuperContestEffect|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\SuperContestEffect", fetch="EAGER")
     */
    protected $superContestEffect;

    /**
     * Use this move before these moves for a Contest Combo.
     *
     * @var MoveInVersionGroup[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", inversedBy="contestUseAfter")
     * @ORM\JoinTable(
     *     name="move_in_version_group_contest_combo",
     *     joinColumns={@ORM\JoinColumn(name="move_in_version_group_first_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="move_in_version_group_second_id", referencedColumnName="id")}
     * )
     */
    protected $contestUseBefore;

    /**
     * Use this move after these moves for a Contest Combo.
     *
     * @var MoveInVersionGroup[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", mappedBy="contestUseBefore")
     */
    protected $contestUseAfter;

    /**
     * Use this move before these moves for a Super Contest Combo.
     *
     * @var MoveInVersionGroup[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", inversedBy="superContestUseAfter")
     * @ORM\JoinTable(
     *     name="move_in_version_group_super_contest_combo",
     *     joinColumns={@ORM\JoinColumn(name="move_in_version_group_first_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="move_in_version_group_second_id", referencedColumnName="id")}
     * )
     */
    protected $superContestUseBefore;

    /**
     * Use this move after these moves for a Super Contest Combo.
     *
     * @var MoveInVersionGroup[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveInVersionGroup", mappedBy="superContestUseBefore")
     */
    protected $superContestUseAfter;

    /**
     * TM/HM Data
     *
     * @var Machine|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Machine", mappedBy="move", cascade={"all"})
     */
    protected $machine;

    /**
     * Move flags
     *
     * @var MoveFlag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveFlag", fetch="EAGER")
     */
    protected $flags;

    /**
     * Stat changes moves (may) make.
     *
     * @var MoveStatChange[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\MoveStatChange", mappedBy="move", cascade={"ALL"}, fetch="EAGER")
     */
    protected $statChanges;

    /**
     * Chance of causing a stat change
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $statChangeChance;

    /**
     * Move Categories
     *
     * @var MoveCategory[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\MoveCategory", fetch="EAGER")
     */
    protected $categories;

    /**
     * Ailment this move can cause
     *
     * @var MoveAilment|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MoveAilment", fetch="EAGER")
     */
    protected $ailment;

    /**
     * Chance of causing an ailment, null if this move cannot cause an ailment.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min="0", max="100")
     */
    protected $ailmentChance;

    /**
     * Number of hits this move can inflict
     *
     * @var Range
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     */
    protected $hits;

    /**
     * Number of turns the user is forced to use this move
     *
     * @var Range
     *
     * @ORM\Embedded(class="App\Entity\Embeddable\Range")
     */
    protected $turns;

    /**
     * HP drain, in percent of damage dealt.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $drain;

    /**
     * Recoil damage, in percent of damage dealt.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $recoil;

    /**
     * Healing, in percent of the user's max HP.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $healing;

    /**
     * Critical hit rate bonus, if any
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $critRateBonus;

    /**
     * Chance of causing flinching
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $flinchChance;

    /**
     * MoveInVersionGroup constructor.
     */
    public function __construct()
    {
        $this->contestUseBefore = new ArrayCollection();
        $this->contestUseAfter = new ArrayCollection();
        $this->superContestUseBefore = new ArrayCollection();
        $this->superContestUseAfter = new ArrayCollection();
        $this->flags = new ArrayCollection();
        $this->statChanges = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * @return Type
     */
    public function getType(): ?Type
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return self
     */
    public function setType(Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPower(): ?int
    {
        return $this->power;
    }

    /**
     * @param int|null $power
     *
     * @return self
     */
    public function setPower(?int $power): self
    {
        $this->power = $power;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPp(): ?int
    {
        return $this->pp;
    }

    /**
     * @param int|null $pp
     *
     * @return self
     */
    public function setPp(?int $pp): self
    {
        $this->pp = $pp;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAccuracy(): ?int
    {
        return $this->accuracy;
    }

    /**
     * @param int|null $accuracy
     *
     * @return self
     */
    public function setAccuracy(?int $accuracy): self
    {
        $this->accuracy = $accuracy;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return self
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return MoveTarget
     */
    public function getTarget(): ?MoveTarget
    {
        return $this->target;
    }

    /**
     * @param MoveTarget $target
     *
     * @return self
     */
    public function setTarget(MoveTarget $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return MoveDamageClass|null
     */
    public function getDamageClass(): ?MoveDamageClass
    {
        return $this->damageClass;
    }

    /**
     * @param MoveDamageClass|null $damageClass
     *
     * @return self
     */
    public function setDamageClass(?MoveDamageClass $damageClass): self
    {
        $this->damageClass = $damageClass;

        return $this;
    }

    /**
     * @return MoveEffectInVersionGroup
     */
    public function getEffect(): ?MoveEffectInVersionGroup
    {
        return $this->effect;
    }

    /**
     * @param MoveEffectInVersionGroup $effect
     *
     * @return self
     */
    public function setEffect(MoveEffectInVersionGroup $effect): self
    {
        $this->effect = $effect;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEffectChance(): ?int
    {
        return $this->effectChance;
    }

    /**
     * @param int|null $effectChance
     *
     * @return self
     */
    public function setEffectChance(?int $effectChance): self
    {
        $this->effectChance = $effectChance;

        return $this;
    }

    /**
     * @return ContestType|null
     */
    public function getContestType(): ?ContestType
    {
        return $this->contestType;
    }

    /**
     * @param ContestType|null $contestType
     *
     * @return self
     */
    public function setContestType(?ContestType $contestType): self
    {
        $this->contestType = $contestType;

        return $this;
    }

    /**
     * @return ContestEffect|null
     */
    public function getContestEffect(): ?ContestEffect
    {
        return $this->contestEffect;
    }

    /**
     * @param ContestEffect|null $contestEffect
     *
     * @return self
     */
    public function setContestEffect(?ContestEffect $contestEffect): self
    {
        $this->contestEffect = $contestEffect;

        return $this;
    }

    /**
     * @return SuperContestEffect|null
     */
    public function getSuperContestEffect(): ?SuperContestEffect
    {
        return $this->superContestEffect;
    }

    /**
     * @param SuperContestEffect|null $superContestEffect
     *
     * @return self
     */
    public function setSuperContestEffect(?SuperContestEffect $superContestEffect): self
    {
        $this->superContestEffect = $superContestEffect;

        return $this;
    }

    /**
     * @return MoveInVersionGroup[]|Collection
     */
    public function getContestUseBefore()
    {
        return $this->contestUseBefore;
    }

    /**
     * @return MoveInVersionGroup[]|Collection
     */
    public function getContestUseAfter()
    {
        return $this->contestUseAfter;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function addContestUseAfter(MoveInVersionGroup $move): self
    {
        if (!$this->contestUseAfter->contains($move)) {
            $this->contestUseAfter->add($move);
            $move->addContestUseBefore($this);
        }

        return $this;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function addContestUseBefore(MoveInVersionGroup $move): self
    {
        if (!$this->contestUseBefore->contains($move)) {
            $this->contestUseBefore->add($move);
        }

        return $this;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function removeContestUseAfter(MoveInVersionGroup $move): self
    {
        if ($this->contestUseAfter->contains($move)) {
            $this->contestUseAfter->removeElement($move);
            $move->removeContestUseBefore($this);
        }

        return $this;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function removeContestUseBefore(MoveInVersionGroup $move): self
    {
        if ($this->contestUseBefore->contains($move)) {
            $this->contestUseBefore->removeElement($move);
        }

        return $this;
    }

    /**
     * @return MoveInVersionGroup[]|Collection
     */
    public function getSuperContestUseBefore()
    {
        return $this->superContestUseBefore;
    }

    /**
     * @return MoveInVersionGroup[]|Collection
     */
    public function getSuperContestUseAfter()
    {
        return $this->superContestUseAfter;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function addSuperContestUseAfter(MoveInVersionGroup $move): self
    {
        if (!$this->superContestUseAfter->contains($move)) {
            $this->superContestUseAfter->add($move);
            $move->addSuperContestUseBefore($this);
        }

        return $this;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function addSuperContestUseBefore(MoveInVersionGroup $move): self
    {
        if (!$this->superContestUseBefore->contains($move)) {
            $this->superContestUseBefore->add($move);
        }

        return $this;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function removeSuperContestUseAfter(MoveInVersionGroup $move): self
    {
        if ($this->superContestUseAfter->contains($move)) {
            $this->superContestUseAfter->removeElement($move);
            $move->removeSuperContestUseBefore($this);
        }

        return $this;
    }

    /**
     * @param MoveInVersionGroup $move
     *
     * @return self
     */
    public function removeSuperContestUseBefore(MoveInVersionGroup $move): self
    {
        if ($this->superContestUseBefore->contains($move)) {
            $this->superContestUseBefore->removeElement($move);
        }

        return $this;
    }

    /**
     * @return Machine|null
     */
    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    /**
     * @param Machine|null $machine
     *
     * @return self
     */
    public function setMachine(?Machine $machine): self
    {
        $this->machine = $machine;
        $machine->setMove($this);

        return $this;
    }

    /**
     * @return MoveFlag[]|Collection
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param MoveFlag $move
     *
     * @return self
     */
    public function addFlag(MoveFlag $move): self
    {
        if (!$this->flags->contains($move)) {
            $this->flags->add($move);
        }

        return $this;
    }

    /**
     * @param MoveFlag $move
     *
     * @return self
     */
    public function removeFlag(MoveFlag $move): self
    {
        if ($this->flags->contains($move)) {
            $this->flags->removeElement($move);
        }

        return $this;
    }

    /**
     * @return MoveStatChange[]|Collection
     */
    public function getStatChanges()
    {
        return $this->statChanges;
    }

    /**
     * @param MoveStatChange $statChange
     *
     * @return self
     */
    public function addStatChange(MoveStatChange $statChange): self
    {
        if (!$this->statChanges->contains($statChange)) {
            $this->statChanges->add($statChange);
            $statChange->setMove($this);
        }

        return $this;
    }

    /**
     * @param MoveStatChange $statChange
     *
     * @return self
     */
    public function removeStatChange(MoveStatChange $statChange): self
    {
        if ($this->statChanges->contains($statChange)) {
            $this->statChanges->removeElement($statChange);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatChangeChance(): ?int
    {
        return $this->statChangeChance;
    }

    /**
     * @param int|null $statChangeChance
     *
     * @return self
     */
    public function setStatChangeChance(?int $statChangeChance): self
    {
        $this->statChangeChance = $statChangeChance;

        return $this;
    }

    /**
     * @return MoveCategory[]|Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param MoveCategory $category
     *
     * @return self
     */
    public function addCategory(MoveCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * @param MoveCategory $category
     *
     * @return self
     */
    public function removeCategory(MoveCategory $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    /**
     * @return MoveAilment|null
     */
    public function getAilment(): ?MoveAilment
    {
        return $this->ailment;
    }

    /**
     * @param MoveAilment|null $ailment
     *
     * @return self
     */
    public function setAilment(?MoveAilment $ailment): self
    {
        $this->ailment = $ailment;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAilmentChance(): ?int
    {
        return $this->ailmentChance;
    }

    /**
     * @param int|null $ailmentChance
     *
     * @return self
     */
    public function setAilmentChance(?int $ailmentChance): self
    {
        $this->ailmentChance = $ailmentChance;

        return $this;
    }

    /**
     * @return Range
     */
    public function getHits(): Range
    {
        return $this->hits;
    }

    /**
     * @param Range $hits
     *
     * @return self
     */
    public function setHits(Range $hits): self
    {
        $this->hits = $hits;

        return $this;
    }

    /**
     * @return Range
     */
    public function getTurns(): Range
    {
        return $this->turns;
    }

    /**
     * @param Range $turns
     *
     * @return self
     */
    public function setTurns(Range $turns): self
    {
        $this->turns = $turns;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDrain(): ?int
    {
        return $this->drain;
    }

    /**
     * @param int|null $drain
     *
     * @return self
     */
    public function setDrain(?int $drain): self
    {
        $this->drain = $drain;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecoil(): ?int
    {
        return $this->recoil;
    }

    /**
     * @param int|null $recoil
     *
     * @return self
     */
    public function setRecoil(?int $recoil): self
    {
        $this->recoil = $recoil;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHealing(): ?int
    {
        return $this->healing;
    }

    /**
     * @param int|null $healing
     *
     * @return self
     */
    public function setHealing(?int $healing): self
    {
        $this->healing = $healing;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCritRateBonus(): ?int
    {
        return $this->critRateBonus;
    }

    /**
     * @param int|null $critRateBonus
     *
     * @return self
     */
    public function setCritRateBonus(?int $critRateBonus): self
    {
        $this->critRateBonus = $critRateBonus;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFlinchChance(): ?int
    {
        return $this->flinchChance;
    }

    /**
     * @param int|null $flinchChance
     *
     * @return self
     */
    public function setFlinchChance(?int $flinchChance): self
    {
        $this->flinchChance = $flinchChance;

        return $this;
    }
}
