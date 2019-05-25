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
     * @var string|null
     */
    protected $schema = null;

    /**
     * @var string|null
     */
    protected $id = null;

    /**
     * @var string|null
     */
    protected $relativeId = null;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var AbstractSchemaType|null
     */
    protected $parent = null;

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
     * @var string[]|null
     */
    protected $examples;

    /**
     * @var string|null
     */
    protected $exampleFormat;

    /**
     * @var array|null
     */
    protected $enum;

    /**
     * @var mixed|null
     */
    protected $const;

    /**
     * AbstractSchemaType constructor.
     *
     * @param object $schema
     */
    public function __construct(object $schema)
    {
        $this->apply($schema);
    }

    /**
     * @param object $schema
     */
    public function apply(object $schema): void
    {
        $this->schema = $schema->{'$schema'} ?? null;
        $this->type = $schema->type ?? null;
        $this->title = $schema->title ?? null;
        $this->description = $schema->description ?? null;
        $this->default = $schema->default ?? null;
        $this->examples = $schema->examples ?? null;
        $this->exampleFormat = $schema->_example_format ?? null;
        $this->enum = $schema->enum ?? null;
        $this->const = $schema->const ?? null;

        if (isset($schema->{'$id'})) {
            $this->id = rtrim($schema->{'$id'}, '#');
        }
        if (isset($schema->{'$relativeId'})) {
            $this->relativeId = rtrim($schema->{'$relativeId'}, '#');
        }
    }

    /**
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param bool $withExtension
     *
     * @return string|null
     */
    public function getRelativeId(bool $withExtension = true): ?string
    {
        if ($withExtension) {
            return $this->relativeId;
        }

        return substr($this->relativeId, 0, strpos($this->relativeId, '.json'));
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
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
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return string[]
     */
    public function getExamples(): ?array
    {
        return $this->examples;
    }

    /**
     * @return string|null
     */
    public function getExampleFormat(): ?string
    {
        return $this->exampleFormat;
    }

    /**
     * @return array|null
     */
    public function getEnum(): ?array
    {
        return $this->enum;
    }

    /**
     * @return mixed|null
     */
    public function getConst()
    {
        return $this->const;
    }

    /**
     * Find the root schema object.
     *
     * @param AbstractSchemaType $parent
     *
     * @return AbstractSchemaType
     */
    protected function findRootSchema(AbstractSchemaType $parent)
    {
        if ($parent->getParent() === null) {
            return $parent;
        }

        return $this->findRootSchema($parent->getParent());
    }

    /**
     * @return AbstractSchemaType|null
     */
    public function getParent(): ?AbstractSchemaType
    {
        return $this->parent;
    }

    /**
     * @param AbstractSchemaType|null $parent
     *
     * @return self
     */
    public function setParent(?AbstractSchemaType $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Is this array a list (true) or map (false)?
     *
     * Because JSON distinguishes between lists and maps while PHP does not,
     * this method will determine if a behavior change is needed for the
     * numerous places in the spec where this matters.
     *
     * @param array $list
     *
     * @return bool
     */
    protected function isList(array $list): bool
    {
        return array_keys($list) === range(0, count($list) - 1);
    }
}
