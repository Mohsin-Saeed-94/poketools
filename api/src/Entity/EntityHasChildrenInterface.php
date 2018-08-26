<?php


namespace App\Entity;


use Doctrine\Common\Collections\Collection;

/**
 * Entities with children.
 *
 * This is the counterpart to App\Entity\EntityHasParentInterface.
 */
interface EntityHasChildrenInterface
{

    /**
     * @return Collection|EntityHasParentInterface[]
     */
    public function getChildren(): Collection;

    /**
     * @param EntityHasParentInterface $child
     *
     * @return self
     */
    public function addChild(EntityHasParentInterface $child);

    /**
     * @param EntityHasParentInterface $child
     *
     * @return self
     */
    public function removeChild(EntityHasParentInterface $child);

    /**
     * @param GroupableInterface $group
     *
     * @return EntityGroupedInterface|null
     */
    public function findChildByGrouping(GroupableInterface $group);
}
