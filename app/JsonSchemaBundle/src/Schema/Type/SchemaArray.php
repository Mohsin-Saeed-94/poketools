<?php
/**
 * @file SchemaArray.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type;

use DragoonBoots\JsonSchemaBundle\Schema\SchemaType;

/**
 * "array" type
 */
class SchemaArray extends AbstractSchemaCollectionType
{
    /**
     * All items must be validated by a single schema in $items
     */
    public const VALIDATION_TYPE_LIST = 'list';

    /**
     * All items must be validated the the schema in $items in the same position.
     */
    public const VALIDATION_TYPE_TUPLE = 'tuple';

    /**
     * One of the VALIDATION_TYPE_* constants, or null if no schema validation
     * is to take place.
     *
     * @var string|null
     */
    protected $validationType = null;

    /**
     * One of:
     * - A list of SchemaTypes if $validationType is VALIDATION_TYPE_TUPLE
     * - A SchemaType if $validationType is VALIDATION_TYPE_LIST
     * - null if $validationType is null and no schema validation will be
     *   performed.
     *
     * @var AbstractSchemaType[]|AbstractSchemaType|null
     */
    protected $items;

    /**
     * One of:
     * - true/false if additional items not defined in $items are allowed (or not)
     * - A SchemaType that additional items must match.
     *
     * @var AbstractSchemaType|bool
     */
    protected $additionalItems = true;

    /**
     * @var int|null
     */
    protected $maxItems;

    /**
     * @var int|null
     */
    protected $minItems;

    /**
     * @var bool|null
     */
    protected $uniqueItems;

    /**
     * @var AbstractSchemaType|null
     */
    protected $contains;

    /**
     * SchemaArray constructor.
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

        $this->maxItems = $schema->maxItems ?? null;
        $this->minItems = $schema->minItems ?? null;
        $this->uniqueItems = $schema->uniqueItems ?? null;

        if (isset($schema->items)) {
            // Determine the validation type
            if (is_object($schema->items)) {
                // List validation
                $this->validationType = self::VALIDATION_TYPE_LIST;
                $child = SchemaType::create($schema->items);
                $child->setParent($this);
                $this->items = $child;
                $this->additionalItems = true;
            } else {
                // Tuple validation
                $this->validationType = self::VALIDATION_TYPE_TUPLE;
                $this->items = [];
                foreach ($schema->items as $item) {
                    $child = SchemaType::create($item);
                    $child->setParent($this);
                    $this->items[] = $child;
                }
                if (isset($schema->additionalItems)) {
                    if (is_bool($schema->additionalItems)) {
                        $this->additionalItems = $schema->additionalItems;
                    } elseif (is_object($schema->additionalItems)) {
                        // Validate additional items
                        $child = SchemaType::create($schema->additionalItems);
                        $child->setParent($this);
                        $this->additionalItems = $child;
                    }
                }
            }
        }

        if (isset($schema->contains)) {
            $child = SchemaType::create($schema->contains);
            $child->setParent($this);
            $this->contains = $child;
        }
    }

    /**
     * @return string|null
     */
    public function getValidationType(): ?string
    {
        return $this->validationType;
    }

    /**
     * @return AbstractSchemaType|AbstractSchemaType[]|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return bool|AbstractSchemaType
     */
    public function getAdditionalItems()
    {
        return $this->additionalItems;
    }

    /**
     * @return int|null
     */
    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    /**
     * @return int|null
     */
    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    /**
     * @return bool|null
     */
    public function getUniqueItems(): ?bool
    {
        return $this->uniqueItems;
    }

    /**
     * @return AbstractSchemaType|null
     */
    public function getContains(): ?AbstractSchemaType
    {
        return $this->contains;
    }
}
