<?php
/**
 * @file SchemaNumber.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type;

/**
 * "number" type
 */
class SchemaNumber extends AbstractSchemaNumberType
{
    /**
     * @var float|null
     */
    protected $multipleOf;

    /**
     * @var float|null
     */
    protected $minimum;

    /**
     * @var float|null
     */
    protected $exclusiveMinimum;

    /**
     * @var float|null
     */
    protected $maximum;

    /**
     * @var float|null
     */
    protected $exclusiveMaximum;

    /**
     * @return float|null
     */
    public function getMultipleOf(): ?float
    {
        return $this->multipleOf;
    }

    /**
     * @return float|null
     */
    public function getMinimum(): ?float
    {
        return $this->minimum;
    }

    /**
     * @return float|null
     */
    public function getExclusiveMinimum(): ?float
    {
        return $this->exclusiveMinimum;
    }

    /**
     * @return float|null
     */
    public function getMaximum(): ?float
    {
        return $this->maximum;
    }

    /**
     * @return float|null
     */
    public function getExclusiveMaximum(): ?float
    {
        return $this->exclusiveMaximum;
    }
}
