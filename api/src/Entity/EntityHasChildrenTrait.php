<?php


namespace App\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * Default implementation of App\Entity\EntityHasChildrenInterface
 *
 * Classes using this trait will still need to define the $children property
 * and its mappings for the ORM.  This trait only implements the boilerplate
 * getter/setter methods.
 */
trait EntityHasChildrenTrait
{

    /**
     * @var Collection
     */
    protected $children;

    /**
     * @return Collection|mixed[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param EntityHasParentInterface[] $children
     *
     * @return \self
     */
    public function addChildren($children)
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * @param EntityHasParentInterface $child
     *
     * @return self
     */
    public function addChild(EntityHasParentInterface $child)
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @param EntityHasParentInterface[] $children
     *
     * @return self
     */
    public function removeChildren($children)
    {
        foreach ($children as $child) {
            $this->removeChild($child);
        }

        return $this;
    }

    /**
     * @param EntityHasParentInterface $child
     *
     * @return self
     */
    public function removeChild(EntityHasParentInterface $child)
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * @param GroupableInterface $group
     *
     * @return EntityGroupedInterface|null
     */
    public function findChildByGrouping(GroupableInterface $group)
    {
        $children = $this->children->filter(
            function (EntityGroupedInterface $entity) use ($group) {
                return ($entity->getGroup() === $group);
            }
        );

        if ($children->isEmpty()) {
            return null;
        } else {
            return $children->first();
        }
    }
}
