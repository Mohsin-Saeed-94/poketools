<?php

use DragoonBoots\JsonSchemaBundle\Schema\SchemaType;
use DragoonBoots\JsonSchemaBundle\Schema\Type\AbstractSchemaType;

/**
 * @file MergeSchema.php
 */
class MergeSchema extends AbstractSchemaType
{
    /**
     * The schema this merge would be applied to.
     *
     * @var AbstractSchemaType
     */
    protected $appliedTo;

    /**
     * A schema describing the changes
     *
     * @var AbstractSchemaType
     */
    protected $changes;

    /**
     * Is the type changed by the merge?
     *
     * @var bool
     */
    protected $changeType;

    /**
     * MergeSchema constructor.
     *
     * @param object $schema
     * @param AbstractSchemaType $appliedTo
     */
    public function __construct(object $schema, AbstractSchemaType $appliedTo)
    {
        // Borrow some properties to assist with presentation.
        $this->title = $appliedTo->getTitle();
        $this->description = $appliedTo->getDescription();

        parent::__construct($schema);

        $this->appliedTo = $appliedTo;

        // If there is no type change, take it from the destination of the merge
        if (!isset($schema->type)) {
            $schema->type = $appliedTo->getType();
            $this->changeType = false;
        } else {
            $this->changeType = true;
        }
        $this->changes = SchemaType::create($schema);
    }
}
