<?php
/**
 * @file PoketoolsCommonMarkExtension.php
 */

namespace App\CommonMark\Extension;


use App\CommonMark\Block\Element\CallableBlock;
use App\CommonMark\Block\Parser\CallableParser;
use App\CommonMark\Block\Renderer\CallableRenderer;
use App\CommonMark\Inline\Parser\CloseBracketInternalLinkParser;
use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * CommonMark Extension to manage special app-specific pieces.
 */
class PoketoolsCommonMarkExtension implements ExtensionInterface
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

    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment
            ->addBlockParser($this->callableParser)
            ->addBlockRenderer(CallableBlock::class, $this->callableRenderer)
            ->addInlineParser($this->closeBrackerInternalLinkParser, 200);
    }
}
