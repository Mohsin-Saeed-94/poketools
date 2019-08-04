<?php


namespace Todaymade\Daux\Extension\Markdown\Schema;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Cursor;

/**
 * Class SchemaBlock
 */
class SchemaBlock extends AbstractBlock
{
    /**
     * @var string
     */
    private $schemaPath;

    /**
     * @inheritDoc
     */
    public function __construct(string $schemaPath)
    {
        parent::__construct();
        $this->schemaPath = $schemaPath;
    }

    /**
     * Returns true if this block can contain the given block as a child node
     *
     * @param AbstractBlock $block
     *
     * @return bool
     */
    public function canContain(AbstractBlock $block)
    {
        return false;
    }

    /**
     * Returns true if block type can accept lines of text
     *
     * @return bool
     */
    public function acceptsLines()
    {
        return false;
    }

    /**
     * Whether this is a code block
     *
     * @return bool
     */
    public function isCode()
    {
        return true;
    }

    /**
     * @param Cursor $cursor
     *
     * @return bool
     */
    public function matchesNextLine(Cursor $cursor)
    {
        return false;
    }

    /**
     * @return string
     */
    public function getSchemaPath(): string
    {
        return $this->schemaPath;
    }
}
