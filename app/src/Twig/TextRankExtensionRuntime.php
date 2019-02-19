<?php
/**
 * @file TextRankExtensionRuntime.php
 */

namespace App\Twig;


use PhpScience\TextRank\TextRankFacade;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Runtime for \App\Twig\TextRankExtension
 */
class TextRankExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var TextRankFacade
     */
    private $textRank;

    /**
     * TextRankExtensionRuntime constructor.
     *
     * @param TextRankFacade $textRank
     */
    public function __construct(TextRankFacade $textRank)
    {
        $this->textRank = $textRank;
    }

    /**
     * Array of the sentences from the most important part of the text
     *
     * @see TextRankFacade::getHighlights()
     *
     * @param string $text
     *
     * @return array
     */
    public function highlights(string $text): array
    {
        return $this->textRank->getHighlights($text);
    }

    /**
     * Array of the most important keywords
     *
     * @see TextRankFacade::getOnlyKeyWords()
     *
     * @param string $text
     *
     * @return array
     */
    public function keywords(string $text): array
    {
        return $this->textRank->getOnlyKeyWords($text);
    }

    /**
     * The most important sentences from the text
     *
     * @see TextRankFacade::summarizeTextBasic()
     *
     * @param string $text
     *
     * @return string
     */
    public function summarize(string $text): string
    {
        return implode(' ', $this->textRank->summarizeTextBasic($text));
    }
}
