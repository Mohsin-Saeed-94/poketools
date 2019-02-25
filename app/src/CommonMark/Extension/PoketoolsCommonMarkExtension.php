<?php
/**
 * @file PoketoolsCommonMarkExtension.php
 */

namespace App\CommonMark\Extension;


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
     * PoketoolsCommonMarkExtension constructor.
     *
     * @param CloseBracketInternalLinkParser $closeBracketInternalLinkParser
     */
    public function __construct(CloseBracketInternalLinkParser $closeBracketInternalLinkParser)
    {
        $this->closeBrackerInternalLinkParser = $closeBracketInternalLinkParser;
    }

    /**
     * {@inheritdoc}
     */
    public function getInlineParsers(): array
    {
        $parsers = parent::getInlineParsers();
        foreach ($parsers as &$parser) {
            if ($parser instanceof CloseBracketParser) {
                // Replace the stock CloseBracketParser with one that understands internal links.
                $parser = $this->closeBrackerInternalLinkParser;
            }
        }

        return $parsers;
    }

}
