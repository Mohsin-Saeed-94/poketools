<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * An item category. Not official.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ItemCategoryRepository")
 * @Gedmo\Tree(type="materializedPath")
 */
class ItemCategory extends AbstractDexEntity implements EntityHasNameInterface, EntityHasSlugInterface
{

    use EntityHasNameAndSlugTrait;

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
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Gedmo\TreePath()
     */
    protected $treePath;

    /**
     * @var ItemCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ItemCategory", inversedBy="treeChildren", cascade={"remove"})
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
     * @var Collection|ItemCategory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ItemCategory", mappedBy="treeParent")
     */
    protected $treeChildren;

    /**
     * ItemCategory constructor.
     */
    public function __construct()
    {
        $this->treeChildren = new ArrayCollection();
    }

    /**
     * @return ItemCategory
     */
    public function getTreeParent(): ?ItemCategory
    {
        return $this->treeParent;
    }

    /**
     * @param ItemCategory $treeParent
     *
     * @return self
     */
    public function setTreeParent(?ItemCategory $treeParent): self
    {
        $this->treeParent = $treeParent;

        return $this;
    }

    /**
     * @return ItemCategory[]
     */
    public function getFullTree(): array
    {
        return $this->calcFullTree();
    }

    /**
     * @param array $tree
     *
     * @return ItemCategory[]
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
     * @return ItemCategory
     */
    private function getTreeRoot(): ItemCategory
    {
        if (isset($this->treeParent)) {
            return $this->treeParent->getTreeRoot();
        }

        return $this;
    }

    /**
     * @return ItemCategory|Collection
     */
    public function getTreeChildren()
    {
        return $this->treeChildren;
    }
}
