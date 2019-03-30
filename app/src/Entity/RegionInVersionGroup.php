<?php

namespace App\Entity;

use App\Entity\Media\RegionMap;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Major areas of the world: Kanto, Johto, etc.
 *
 * @ORM\Entity(repositoryClass="App\Repository\RegionInVersionGroupRepository")
 *
 * @method Region getParent()
 * @method self setParent(Region $parent)
 */
class RegionInVersionGroup extends AbstractDexEntity implements EntityHasParentInterface, EntityGroupedByVersionGroupInterface, EntityHasNameInterface, EntityHasSlugInterface, EntityIsSortableInterface
{
    use EntityHasParentTrait;
    use EntityGroupedByVersionGroupTrait;
    use EntityHasNameAndSlugTrait;
    use EntityIsSortableTrait;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="children")
     * @Assert\NotNull()
     */
    protected $parent;

    /**
     * @var Collection|RegionMap[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Media\RegionMap", mappedBy="region", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $maps;

    /**
     * RegionInVersionGroup constructor.
     */
    public function __construct()
    {
        $this->maps = new ArrayCollection();
    }

    /**
     * @return RegionMap[]|Collection
     */
    public function getMaps()
    {
        return $this->maps;
    }

    /**
     * @param RegionMap $map
     *
     * @return self
     */
    public function addMap(RegionMap $map): self
    {
        if (!$this->maps->contains($map)) {
            $this->maps->add($map);
            $map->setRegion($this);
        }

        return $this;
    }

    /**
     * @param RegionMap $map
     *
     * @return self
     */
    public function removeMap(RegionMap $map): self
    {
        if ($this->maps->contains($map)) {
            $this->maps->removeElement($map);
            $map->setRegion(null);
        }

        return $this;
    }
}
