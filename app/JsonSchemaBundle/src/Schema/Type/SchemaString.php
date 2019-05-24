<?php
/**
 * @file SchemaString.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type;

/**
 * "string" type
 */
class SchemaString extends AbstractSchemaType
{
    /**
     * @var int|null
     */
    protected $minLength;

    /**
     * @var int|null
     */
    protected $maxLength;

    /**
     * @var string|null
     */
    protected $pattern;

    /**
     * @var string|null
     */
    protected $format;

    /**
     * SchemaString constructor.
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

        $this->minLength = $schema->minLength ?? null;
        $this->maxLength = $schema->maxLength ?? null;
        $this->pattern = $schema->pattern ?? null;
        $this->format = $schema->format ?? null;
    }

    /**
     * @return int|null
     */
    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    /**
     * @return int|null
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }


}
