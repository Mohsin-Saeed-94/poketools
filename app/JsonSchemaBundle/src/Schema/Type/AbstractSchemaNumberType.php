<?php
/**
 * @file AbstractSchemaNumberType.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type;

/**
 * Base class for "number" and "integer" types
 */
abstract class AbstractSchemaNumberType extends AbstractSchemaType
{
    /**
     * @var float|int|null
     */
    protected $multipleOf;

    /**
     * @var float|int|null
     */
    protected $minimum;

    /**
     * @var float|int|null
     */
    protected $exclusiveMinimum;

    /**
     * @var float|int|null
     */
    protected $maximum;

    /**
     * @var float|int|null
     */
    protected $exclusiveMaximum;

    /**
     * AbstractSchemaNumberType constructor.
     *
     * @param object $schema
     */
    public function __construct(object $schema)
    {
        parent::__construct($schema);

        $this->apply($schema);
    }

    /**
     * @return float|int|null
     */
    abstract public function getMultipleOf();

    /**
     * @return float|int|null
     */
    abstract public function getMinimum();

    /**
     * @return float|int|null
     */
    abstract public function getExclusiveMinimum();

    /**
     * @return float|int|null
     */
    abstract public function getMaximum();

    /**
     * @return float|int|null
     */
    abstract public function getExclusiveMaximum();

    /**
     * @param object $schema
     */
    public function apply(object $schema): void
    {
        parent::apply($schema);

        $this->multipleOf = $schema->multipleOf ?? null;
        $this->minimum = $schema->minimum ?? null;
        $this->exclusiveMinimum = $schema->exclusiveMinumum ?? null;
        $this->maximum = $schema->maximum ?? null;
        $this->exclusiveMaximum = $schema->exclusiveMaximum ?? null;
    }
}
