<?php
/**
 * @file PoketoolsCommonMarkExtension.php
 */

namespace App\CommonMark\Extension;


use App\CommonMark\Block\Element\CallableBlock;
use App\CommonMark\Block\Parser\CallableParser;
use App\CommonMark\Block\Renderer\CallableRenderer;
use App\CommonMark\Inline\Parser\CloseBracketInternalLinkParser;
use League\CommonMark\Extension\CommonMarkCoreExtension;
use League\CommonMark\Inline\Parser\CloseBracketParser;

/**
 * CommonMark Extension to manage special app-specific pieces.
 */
class PoketoolsCommonMarkExtension extends CommonMarkCoreExtension
{
    /**
     * @var CloseBracketInternalLinkParser
     */
    private $closeBrackerInternalLinkParser;

    /**
     * @var CallableParser
     */
    private $callableParser;

    /**
     * @var CallableRenderer
     */
    private $callableRenderer;

    /**
     * PoketoolsCommonMarkExtension constructor.
     *
     * @param CloseBracketInternalLinkParser $closeBracketInternalLinkParser
     * @param CallableParser $controllerParser
     * @param CallableRenderer $callableRenderer
     */
    public function __construct(
        CloseBracketInternalLinkParser $closeBracketInternalLinkParser,
        CallableParser $controllerParser,
        CallableRenderer $callableRenderer
    ) {
        $this->closeBrackerInternalLinkParser = $closeBracketInternalLinkParser;
        $this->callableParser = $controllerParser;
        $this->callableRenderer = $callableRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getInlineParsers(): array
    {
        $parsers = parent::getInlineParsers();

        // Replace the stock CloseBracketParser with one that understands internal links.
        foreach ($parsers as &$parser) {
            if ($parser instanceof CloseBracketParser) {
                $parser = $this->closeBrackerInternalLinkParser;
            }
        }
        unset($parser);

        return $parsers;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockParsers()
    {
        $parsers = parent::getBlockParsers();

        $parsers[] = $this->callableParser;

        return $parsers;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockRenderers()
    {
        $renderers = parent::getBlockRenderers();

        $renderers[CallableBlock::class] = $this->callableRenderer;

        return $renderers;
    }
}
