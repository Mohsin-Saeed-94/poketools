<?php
/**
 * @file SchemaNot.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type\Combination;

/**
 * "not" combination
 */
class SchemaNot extends AbstractSchemaCombination
{
    protected $combinationType = 'not';

    /**
     * SchemaNot constructor.
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

        $this->children = [];
        $this->createChildren([$schema->not]);
    }
}
