<?php
/**
 * @file SchemaTypeInterface.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Type;

use League\CommonMark\CommonMarkConverter;

/**
 * Base class for all schema types
 */
abstract class AbstractSchemaType
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $relativeId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var mixed|null
     */
    protected $default;

    /**
     * @var string[]
     */
    protected $examples;

    /**
     * AbstractSchemaType constructor.
     *
     * @param object $schema
     * @param string $prefix
     */
    public function __construct(object $schema, string $prefix)
    {
        $this->schema = $schema->{'$schema'} ?? 'http://json-schema.org/schema#';
        $this->id = rtrim($schema->{'$id'}, '#');
        $this->relativeId = ltrim(str_replace($prefix, '', $this->id), '/');
        $this->type = $schema->type;
        $this->title = $schema->title ?? null;
        $this->description = $schema->description ?? null;
        $this->default = $schema->default ?? null;
        $this->examples = $schema->examples ?? [];
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRelativeId(): string
    {
        return $this->relativeId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Convert a CommonMark-formatted description to HTML.
     *
     * @return string
     */
    public function getHtmlDescription(): string
    {
        $description = $this->description ?? '';
        $markdownConverter = new CommonMarkConverter();

        return $markdownConverter->convertToHtml($description);
    }

    /**
     * @return mixed|null
     */
    public function getDefault(): ?mixed
    {
        return $this->default;
    }

    /**
     * @return string[]
     */
    public function getExamples(): array
    {
        return $this->examples;
    }
}
