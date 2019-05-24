<?php
/**
 * @file SchemaOneOf.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type\Combination;

/**
 * "oneOf" combination
 */
class SchemaOneOf extends AbstractSchemaCombination
{
    protected $combinationType = 'oneOf';

    /**
     * SchemaOneOf constructor.
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

        $this->createChildren($schema->oneOf);
    }
}
