<?php
/**
 * @file AbstractSchemaCombination.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type\Combination;

use DragoonBoots\JsonSchemaBundle\Schema\SchemaType;
use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaCollectionType;
use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaType;

/**
 * Handle combining schemas
 *
 * @see http://json-schema.org/understanding-json-schema/reference/combining.html
 */
abstract class AbstractSchemaCombination extends AbstractSchemaCollectionType
{
    protected $type = 'combination';

    /**
     * @var AbstractSchemaType[]
     */
    protected $children = [];

    /**
     * @var string
     */
    protected $combinationType;

    /**
     * @return AbstractSchemaType[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param AbstractSchemaType $child
     *
     * @return self
     */
    public function removeChild(AbstractSchemaType $child): self
    {
        $pos = array_search($child, $this->children);
        if ($pos !== false) {
            unset($this->children[$pos]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCombinationType(): string
    {
        return $this->combinationType;
    }

    /**
     * @param object[] $children
     *
     * @return self
     */
    protected function createChildren(array $children): self
    {
        foreach ($children as $child) {
            $this->addChild(SchemaType::create($child));
        }

        return $this;
    }

    /**
     * @param AbstractSchemaType $child
     *
     * @return self
     */
    public function addChild(AbstractSchemaType $child): self
    {
        if (!in_array($child, $this->children)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }
}
