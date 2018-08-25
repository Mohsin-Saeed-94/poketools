<?php


namespace App\Entity;

/**
 * Entities with parents
 *
 * This is mainly useful for grouping grouped objects together, e.g. relating a
 * single ability through all version groups.
 */
interface EntityHasParentInterface
{

    /**
     * @return EntityHasChildrenInterface|null
     */
    public function getParent(): ?EntityHasChildrenInterface;

    /**
     * @param EntityHasChildrenInterface|null $parent
     *
     * @return self
     */
    public function setParent(?EntityHasChildrenInterface $parent);
}
