<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A sub-area of a location. (e.g. 1F, Basement, etc.)
 *
 * @ORM\Entity(repositoryClass="App\Repository\LocationAreaRepository")
 * @Gedmo\Tree(type="materializedPath")
 */
class LocationArea extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface, EntityHasDefaultInterface, EntityIsSortableInterface
{

    use EntityHasNameAndSlugTrait;
    use EntityHasDefaultTrait;
    use EntityIsSortableTrait;

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
     * @var LocationInVersionGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationInVersionGroup", inversedBy="areas")
     * @Assert\NotNull
     */
    protected $location;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    protected $treePath;

    /**
     * @var LocationArea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LocationArea", inversedBy="treeChildren", cascade={"remove"})
     * @Gedmo\TreeParent()
     */
    protected $treeParent;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeLevel()
     */
    protected $treeLevel;

    /**
     * @var Collection|LocationArea[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\LocationArea", mappedBy="treeParent", cascade={"ALL"})
     * @ORM\OrderBy({"position": "ASC"})
     */
    protected $treeChildren;

    /**
     * @var Collection|Shop[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Shop", mappedBy="locationArea", cascade={"ALL"})
     * @ORM\OrderBy({"isDefault": "ASC", "name": "ASC"})
     */
    protected $shops;

    /**
     * LocationArea constructor.
     */
    public function __construct()
    {
        $this->treeChildren = new ArrayCollection();
        $this->shops = new ArrayCollection();
    }

    public static function getGroupField(): string
    {
        return 'location';
    }

    /**
     * @return LocationInVersionGroup
     */
    public function getLocation(): ?LocationInVersionGroup
    {
        return $this->location;
    }

    /**
     * @param LocationInVersionGroup $location
     *
     * @return self
     */
    public function setLocation(?LocationInVersionGroup $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return LocationArea
     */
    public function getTreeParent(): ?LocationArea
    {
        return $this->treeParent;
    }

    /**
     * @param LocationArea $treeParent
     *
     * @return self
     */
    public function setTreeParent(?LocationArea $treeParent): self
    {
        $this->treeParent = $treeParent;

        return $this;
    }

    /**
     * @return LocationArea[]
     */
    public function getFullTree(): array
    {
        return $this->calcFullTree();
    }

    /**
     * @param array $tree
     *
     * @return LocationArea[]
     */
    private function calcFullTree(array &$tree = []): array
    {
        if (empty($tree)) {
            $root = $this->getTreeRoot();
            $tree[] = $root;
            foreach ($root->getTreeChildren() as $child) {
                $child->calcFullTree($tree);
            }
        } else {
            $tree[] = $this;

            foreach ($this->treeChildren as $child) {
                $child->calcFullTree($tree);
            }
        }

        return $tree;
    }

    /**
     * @return LocationArea
     */
    private function getTreeRoot(): LocationArea
    {
        if (isset($this->treeParent)) {
            return $this->treeParent->getTreeRoot();
        }

        return $this;
    }

    /**
     * @return LocationArea[]|Collection
     */
    public function getTreeChildren()
    {
        return $this->treeChildren;
    }

    /**
     * @param LocationArea $child
     *
     * @return self
     */
    public function addTreeChild(LocationArea $child): self
    {
        if (!$this->treeChildren->contains($child)) {
            $this->treeChildren->add($child);
            $child->setTreeParent($this);
        }

        return $this;
    }

    /**
     * @param LocationArea $child
     *
     * @return LocationArea
     */
    public function removeTreeChild(LocationArea $child): self
    {
        if ($this->treeChildren->contains($child)) {
            $this->treeChildren->removeElement($child);
            $child->setTreeParent(null);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTreePath(): string
    {
        return $this->treePath;
    }

    /**
     * @return Shop[]|Collection
     */
    public function getShops(): Collection
    {
        return $this->shops;
    }

    /**
     * @param Shop $shop
     *
     * @return self
     */
    public function addShop(Shop $shop): self
    {
        if (!$this->shops->contains($shop)) {
            $this->shops->add($shop);
            $shop->setLocationArea($this);
        }

        return $this;
    }

    /**
     * @param Shop $shop
     *
     * @return self
     */
    public function removeShop(Shop $shop): self
    {
        if ($this->shops->contains($shop)) {
            $this->shops->removeElement($shop);
            $shop->setLocationArea(null);
        }

        return $this;
    }
}
