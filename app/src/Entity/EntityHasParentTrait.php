<?php


namespace App\Entity;

/**
 * Default implementation of App\Entity\EntityHasParentInterface
 *
 * Classes using this trait will still need to define the $parent property
 * and its mappings for the ORM.  This trait only implements the boilerplate
 * getter/setter methods. */
trait EntityHasParentTrait
{

    /**
     * @var mixed
     */
    protected $parent;

    /**
     * @return EntityHasChildrenInterface|null
     */
    public function getParent(): ?EntityHasChildrenInterface
    {
        return $this->parent;
    }

    /**
     * @param EntityHasChildrenInterface|null $parent
     *
     * @return self
     */
    public function setParent(?EntityHasChildrenInterface $parent)
    {
        $this->parent = $parent;

        return $this;
    }
}
