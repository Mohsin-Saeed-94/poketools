<?php


namespace Todaymade\Daux\Extension\Markdown\Schema;


use League\CommonMark\Block\Parser\BlockParserInterface;
use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;

/**
 * Class SchemaParser
 *
 * Match {{ schema:$path }}
 */
class SchemaParser implements BlockParserInterface
{
    private const RE_SCHEMA = '`{{\s*schema:(\S+)\s*}}`';

    /**
     * @var string
     */
    private $schemaRoot;

    /**
     * SchemaParser constructor.
     *
     * @param string $schemaRoot
     */
    public function __construct(string $schemaRoot)
    {
        $this->schemaRoot = $schemaRoot;
    }

    /**
     * @param ContextInterface $context
     * @param Cursor $cursor
     *
     * @return bool
     */
    public function parse(ContextInterface $context, Cursor $cursor): bool
    {
        $previousState = $cursor->saveState();
        $line = $cursor->match(self::RE_SCHEMA);
        if (!$line) {
            $cursor->restoreState($previousState);

            return false;
        }

        // Make sure the schema exists
        preg_match(self::RE_SCHEMA, $line, $matches);
        $schemaPath = realpath($this->schemaRoot.'/'.trim($matches[1]));
        if ($schemaPath !== false && !is_readable($schemaPath)) {
            user_error('Schema does not exist at '.trim($matches[1]), E_USER_WARNING);
            $cursor->restoreState($previousState);

            return false;
        }

        $context->addBlock(new SchemaBlock($schemaPath));

        return true;
    }
}
