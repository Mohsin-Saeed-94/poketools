<?php
/**
 * @file SchemaAllOf.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type\Combination;

/**
 * "allOf" combination
 */
class SchemaAllOf extends AbstractSchemaCombination
{
    protected $combinationType = 'allOf';

    /**
     * SchemaAllOf constructor.
     *
     * @param object $schema
     */
    public function __construct(object $schema)
    {
        parent::__construct($schema);

        $this->apply($schema);
    }

    /**
     * @param object $schema
     */
    public function apply(object $schema): void
    {
        parent::apply($schema);

        $this->createChildren($schema->allOf);
    }
}
