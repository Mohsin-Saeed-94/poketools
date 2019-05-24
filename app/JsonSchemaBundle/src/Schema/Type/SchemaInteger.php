<?php
/**
 * @file SchemaInteger.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type;

/**
 * "integer" type
 */
class SchemaInteger extends AbstractSchemaNumberType
{
    /**
     * @var int|null
     */
    protected $multipleOf;

    /**
     * @var int|null
     */
    protected $minimum;

    /**
     * @var int|null
     */
    protected $exclusiveMinimum;

    /**
     * @var int|null
     */
    protected $maximum;

    /**
     * @var int|null
     */
    protected $exclusiveMaximum;

    /**
     * @return int|null
     */
    public function getMultipleOf(): ?int
    {
        return $this->multipleOf;
    }

    /**
     * @return int|null
     */
    public function getMinimum(): ?int
    {
        return $this->minimum;
    }

    /**
     * @return int|null
     */
    public function getExclusiveMinimum(): ?int
    {
        return $this->exclusiveMinimum;
    }

    /**
     * @return int|null
     */
    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    /**
     * @return int|null
     */
    public function getExclusiveMaximum(): ?int
    {
        return $this->exclusiveMaximum;
    }
}
