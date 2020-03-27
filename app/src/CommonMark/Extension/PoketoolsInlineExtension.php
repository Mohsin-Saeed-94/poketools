<?php


namespace App\CommonMark\Extension;


use App\CommonMark\Inline\Parser\CloseBracketInternalLinkParser;
use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * CommonMark Extension to manage special app-specific pieces (inlines only).
 */
class PoketoolsInlineExtension implements ExtensionInterface
{
    /**
     * @var CloseBracketInternalLinkParser
     */
    private $closeBrackerInternalLinkParser;

    /**
     * PoketoolsCommonMarkExtension constructor.
     *
     * @param CloseBracketInternalLinkParser $closeBracketInternalLinkParser
     */
    public function __construct(
        CloseBracketInternalLinkParser $closeBracketInternalLinkParser
    ) {
        $this->closeBrackerInternalLinkParser = $closeBracketInternalLinkParser;
    }

    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment
            ->addInlineParser($this->closeBrackerInternalLinkParser, 200);
    }
}
