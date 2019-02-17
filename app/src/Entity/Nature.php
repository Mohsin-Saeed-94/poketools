<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A nature a Pokémon can have, such as Calm or Brave.
 *
 * @ORM\Entity(repositoryClass="App\Repository\NatureRepository")
 */
class Nature extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

    /**
     * The stat that this nature increases by 10%
     *
     * @var Stat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $statIncreased;

    /**
     * The stat that this nature decreases by 10%
     *
     * @var Stat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stat", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $statDecreased;

    /**
     * The Berry flavor the Pokémon likes
     *
     * @var BerryFlavor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFlavor", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $flavorLikes;

    /**
     * The Berry flavor the Pokémon hates
     *
     * @var BerryFlavor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BerryFlavor", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $flavorHates;

    /**
     * @var NatureBattleStylePreference[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NatureBattleStylePreference", mappedBy="nature", cascade={"ALL"}, fetch="EAGER")
     */
    protected $battleStylePreferences;

    /**
     * @var NaturePokeathlonStatChange[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\NaturePokeathlonStatChange", mappedBy="nature", cascade={"ALL"}, fetch="EAGER")
     */
    protected $pokeathlonStatChanges;

    /**
     * Nature constructor.
     */
    public function __construct()
    {
        $this->battleStylePreferences = new ArrayCollection();
        $this->pokeathlonStatChanges = new ArrayCollection();
    }

    /**
     * @param NatureBattleStylePreference $battleStylePreference
     *
     * @return self
     */
    public function addBattleStylePreference(NatureBattleStylePreference $battleStylePreference): self
    {
        if (!$this->battleStylePreferences->contains($battleStylePreference)) {
            $this->battleStylePreferences->add($battleStylePreference);
            $battleStylePreference->setNature($this);
        }

        return $this;
    }

    /**
     * @param NatureBattleStylePreference $battleStylePreference
     *
     * @return self
     */
    public function removeBattleStylePreferences(NatureBattleStylePreference $battleStylePreference): self
    {
        if ($this->battleStylePreferences->contains($battleStylePreference)) {
            $this->battleStylePreferences->removeElement($battleStylePreference);
            $battleStylePreference->setNature(null);
        }

        return $this;
    }

    /**
     * @return NatureBattleStylePreference[]|Collection
     */
    public function getBattleStylePreferences()
    {
        return $this->battleStylePreferences;
    }

    /**
     * @param NaturePokeathlonStatChange $pokeathlonStatChange
     *
     * @return self
     */
    public function addPokeathlonStatChange(NaturePokeathlonStatChange $pokeathlonStatChange): self
    {
        if (!$this->pokeathlonStatChanges->contains($pokeathlonStatChange)) {
            $this->pokeathlonStatChanges->add($pokeathlonStatChange);
            $pokeathlonStatChange->setNature($this);
        }

        return $this;
    }

    /**
     * @param NaturePokeathlonStatChange $pokeathlonStatChange
     *
     * @return self
     */
    public function removePokeathlonStatChange(NaturePokeathlonStatChange $pokeathlonStatChange): self
    {
        if ($this->pokeathlonStatChanges->contains($pokeathlonStatChange)) {
            $this->pokeathlonStatChanges->removeElement($pokeathlonStatChange);
            $pokeathlonStatChange->setNature(null);
        }

        return $this;
    }

    /**
     * @return NaturePokeathlonStatChange[]|Collection
     */
    public function getPokeathlonStatChanges()
    {
        return $this->pokeathlonStatChanges;
    }

    /**
     *
     * A Nature is neutral if it does not affect any stats or flavor preferences
     *
     * @return bool
     */
    public function isNeutral(): bool
    {
        return ($this->getStatIncreased() === $this->getStatDecreased()
            && $this->getFlavorLikes() === $this->getFlavorHates());
    }

    /**
     * @return Stat
     */
    public function getStatIncreased(): ?Stat
    {
        return $this->statIncreased;
    }

    /**
     * @param Stat $statIncreased
     *
     * @return self
     */
    public function setStatIncreased(Stat $statIncreased): self
    {
        $this->statIncreased = $statIncreased;

        return $this;
    }

    /**
     * @return Stat
     */
    public function getStatDecreased(): ?Stat
    {
        return $this->statDecreased;
    }

    /**
     * @param Stat $statDecreased
     *
     * @return self
     */
    public function setStatDecreased(Stat $statDecreased): self
    {
        $this->statDecreased = $statDecreased;

        return $this;
    }

    /**
     * @return BerryFlavor
     */
    public function getFlavorLikes(): ?BerryFlavor
    {
        return $this->flavorLikes;
    }

    /**
     * @param BerryFlavor $flavorLikes
     *
     * @return self
     */
    public function setFlavorLikes(BerryFlavor $flavorLikes): self
    {
        $this->flavorLikes = $flavorLikes;

        return $this;
    }

    /**
     * @return BerryFlavor
     */
    public function getFlavorHates(): ?BerryFlavor
    {
        return $this->flavorHates;
    }

    /**
     * @param BerryFlavor $flavorHates
     *
     * @return self
     */
    public function setFlavorHates(BerryFlavor $flavorHates): self
    {
        $this->flavorHates = $flavorHates;

        return $this;
    }
}
