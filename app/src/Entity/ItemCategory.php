<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @return ItemCategory|Collection
     */
    public function getTreeChildren()
    {
        return $this->treeChildren;
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
}
