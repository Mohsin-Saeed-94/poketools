<?php
/**
 * @file SchemaAnyOf.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type\Combination;

/**
 * "anyOf" combination
 */
class SchemaAnyOf extends AbstractSchemaCombination
{
    protected $combinationType = 'anyOf';

    /**
     * SchemaAnyOf constructor.
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

        $this->createChildren($schema->anyOf);
    }
}
