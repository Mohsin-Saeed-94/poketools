<?php


namespace Todaymade\Daux\Extension;


use League\CommonMark\Environment;
use Todaymade\Daux\Extension\Markdown\DescriptionList\DescriptionListBlock;
use Todaymade\Daux\Extension\Markdown\DescriptionList\DescriptionListItem;
use Todaymade\Daux\Extension\Markdown\DescriptionList\DescriptionListItemRenderer;
use Todaymade\Daux\Extension\Markdown\DescriptionList\DescriptionListParser;
use Todaymade\Daux\Extension\Markdown\DescriptionList\DescriptionListRenderer;
use Todaymade\Daux\Extension\Markdown\Schema\SchemaBlock;
use Todaymade\Daux\Extension\Markdown\Schema\SchemaParser;
use Todaymade\Daux\Extension\Markdown\Schema\SchemaRenderer;
use Todaymade\Daux\Processor as BaseProcessor;
use Todaymade\Daux\Tree\Content;
use Todaymade\Daux\Tree\Directory;
use Todaymade\Daux\Tree\Root;

/**
 * Class PoketoolsProcessor
 *
 * @package Todaymade\Daux\Extension
 */
class PoketoolsProcessor extends BaseProcessor
{
    private const SCHEMA_ROOT = __DIR__.'/../../app/resources/schema';
    private const INCLUDES_ROOT = __DIR__.'/../inc';
    private const RE_INCLUDE = '`{{\s*include:(?P<path>.+?)(?P<ext>\.md)?\s*}}`';

    /**
     * @inheritDoc
     */
    public function extendCommonMarkEnvironment(Environment $environment)
    {
        parent::extendCommonMarkEnvironment($environment);

        $environment
            ->addBlockParser(new DescriptionListParser())
            ->addBlockRenderer(DescriptionListItem::class, new DescriptionListItemRenderer())
            ->addBlockRenderer(DescriptionListBlock::class, new DescriptionListRenderer())
            ->addBlockParser(new SchemaParser(self::SCHEMA_ROOT))
            ->addBlockRenderer(SchemaBlock::class, new SchemaRenderer());
    }

    /**
     * @inheritDoc
     */
    public function manipulateTree(Root $root)
    {
        $this->walkPages($root);
    }

    /**
     * @param Root|Directory $root
     */
    private function walkPages($root)
    {
        foreach ($root->getEntries() as $entry) {
            if ($entry instanceof Directory) {
                $this->walkPages($entry);
            } elseif ($entry instanceof Content) {
                $this->processPage($entry);
            }
        }
    }

    /**
     * @param Content $content
     */
    private function processPage(Content $content)
    {
        // Process include directives (i.e. {{ include:something.md }}
        $this->processIncludes($content);

        // Add schema info to schema pages
        $schema = $content->getAttribute('schema');
        if ($schema !== null) {
            $this->processSchemaPage($content, $schema);
        }
    }

    /**
     * @param Content $content
     */
    private function processIncludes(Content $content)
    {
        preg_match_all(self::RE_INCLUDE, $content->getContent(), $matches, PREG_SET_ORDER);
        $needles = [];
        $replacements = [];
        foreach ($matches as $match) {
            $current = $match[0];
            $path = $match['path'];
            $path = trim($path);
            $path = trim($path, '/');
            $path = self::INCLUDES_ROOT.'/'.$path;
            if (!isset($match['ext'])) {
                $path .= '.md';
            }
            if (is_readable($path)) {
                $needles[] = $current;
                $replacements[] = file_get_contents($path);
            }
        }
        $content->setContent(str_replace($needles, $replacements, $content->getContent()));
    }

    /**
     * @param Content $content
     * @param string $schema
     */
    private function processSchemaPage(Content $content, string $schema): void
    {
        $schemaContent = $this->parseSchema($schema);

        // Set the page title
        if (isset($schemaContent['title'])) {
            $content->setTitle($schemaContent['title']);
        }

        $summary = [];
        // Add description at the beginning of the page
        if ($schemaContent['description']) {
            $summary['Description'] = $schemaContent['description'];
        }

        // Create a link to the data location
        $dataPathRoot = 'resources/data';
        if ($content->getAttribute('format') !== null) {
            switch ($content->getAttribute('format')) {
                case 'yaml':
                    $dataPath = $content->getName().'/';
                    break;

                case 'csv':
                    $dataPath = $content->getName().'.csv';
                    break;
                default:
                    $dataPath = null;
            }
        } else {
            $dataPath = null;
        }
        if ($dataPath) {
            $dataPath = $dataPathRoot.'/'.$dataPath;
            $summary['Data path'] = '<a href="https://gitlab.com/gamestuff.info/poketools/tree/master/app/'.$dataPath.'">'.$dataPath.'</a>';
        }

        $summaryPieces = [];
        foreach ($summary as $name => $value) {
            $summaryPieces[] = sprintf('<dt>%s</dt><dd>%s</dd>', $name, $value);
        }
        $content->setContent(
            trim(
                implode(
                    "\n\n",
                    [
                        $summaryPieces ? "# Summary\n\n<dl>".implode("\n", $summaryPieces).'</dl>' : '',
                        $content->getContent(),
                        "# JSON Schema\n\n{{ schema:".$content->getName().'.json }}',
                    ]
                )
            )
        );
    }

    /**
     * @param string $schemaPath
     *
     * @return array
     */
    private function parseSchema(string $schemaPath): array
    {
        $schemaString = file_get_contents(self::SCHEMA_ROOT.'/'.$schemaPath);

        return json_decode($schemaString, true);
    }
}
