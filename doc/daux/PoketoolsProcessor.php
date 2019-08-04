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
use Todaymade\Daux\Tree\Entry;
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
        $this->walkSchemas($root);
        $this->walkPages($root);
    }

    /**
     * @param Root|Directory $root
     */
    private function walkSchemas($root)
    {
        foreach ($root->getEntries() as $entry) {
            if ($entry instanceof Directory) {
                $this->walkSchemas($entry);
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
        $content->setContent($this->resolveIncludes($content->getContent()));
    }

    /**
     * Handle {{ include:thing }}
     *
     * @param string $content
     *
     * @return string
     */
    private function resolveIncludes(string $content): string
    {
        if (!preg_match_all(self::RE_INCLUDE, $content, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL)) {
            return $content;
        }

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
                $replacements[] = $this->resolveIncludes(file_get_contents($path));
            }
        }

        return str_replace($needles, $replacements, $content);
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
            $summary['Data path'] = sprintf(
                '[%s](https://gitlab.com/gamestuff.info/poketools/tree/master/app/%s)',
                $dataPath,
                $dataPath
            );
        }

        $summaryPieces = [];
        foreach ($summary as $name => $value) {
            $summaryPieces[] = sprintf(':%s: %s', $name, $value);
        }
        $content->setContent(
            trim(
                implode(
                    "\n\n",
                    [
                        $summaryPieces ? "# Summary\n\n".implode("\n", $summaryPieces) : '',
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

    /**
     * @param Root|Directory $root
     */
    private function walkPages($root)
    {
        foreach ($root->getEntries() as $entry) {
            if ($entry instanceof Directory) {
                $this->walkPages($entry);
            } elseif ($entry instanceof Content) {
                $this->processTocTree($entry);
            }
        }
    }

    /**
     * Handle {{ toctree }}
     *
     * @param Content $content
     */
    private function processTocTree(Content $content)
    {
        if (!preg_match_all(
            '`{{\s*toctree:?(?P<path>\S.*)?\s*}}`',
            $content->getContent(),
            $matches,
            PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL
        )) {
            return;
        }

        $needles = [];
        $replacements = [];
        foreach ($matches as $match) {
            $current = $match[0];
            $parent = $content->getParent();
            if (isset($match['path'])) {
                // Path specified, get the node requested
                $path = $match['path'];
                $path = trim($path);
                $path = rtrim($path, '/');

                // Determine the path - is this relative or absolute?
                if (strpos($path, '/') === 0) {
                    // From root of tree
                    while (!$parent instanceof Root) {
                        $parent = $parent->getParent();
                    }
                }
                $path = ltrim($path, '/');
                $pathParts = explode('/', $path);
                foreach ($pathParts as $pathPart) {
                    if ($pathPart === '..') {
                        // Up one level, if possible
                        if ($parent instanceof Root) {
                            user_error(
                                sprintf('Cannot generate toctree as path "%s" goes above the root.', $match['path'])
                            );
                            continue 2;
                        }
                        $parent = $parent->getParent();
                    } else {
                        if (!$parent->offsetExists($pathPart)) {
                            user_error('Cannot generate toctree as path "%s" does not exist.', $match['path']);
                            continue 2;
                        }
                        $parent = $parent->offsetGet($pathPart);
                    }
                }
            }
            $tree = $this->buildTocTree($parent);
            $needles[] = $current;
            $replacements[] = $tree;
        }

        $content->setContent(str_replace($needles, $replacements, $content->getContent()));
    }

    /**
     * @param Entry $parent
     * @param int $indent
     *
     * @return string
     */
    private function buildTocTree(Entry $parent, int $indent = 0): string
    {
        $bullet = '- ';
        $treeLinks = [];
        foreach ($parent as $item) {
            if ($item instanceof Content) {
                $treeLinks[] = implode(
                    '',
                    [
                        str_repeat(' ', $indent),
                        $bullet,
                        sprintf('[%s](%s)', $item->getTitle(), $item->getUrl()),
                    ]
                );
            } else {
                $treeLinks[] = $this->buildTocTree($item, $indent + strlen($bullet));
            }
        }

        return implode("\n", $treeLinks);
    }
}
