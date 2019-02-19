<?php
/**
 * @file SummaryColumn.php
 */

namespace App\DataTable\Column;


use Omines\DataTablesBundle\Column\TextColumn;
use PhpScience\TextRank\TextRankFacade;

/**
 * TextColumn that will auto-summarize its contents.
 */
class SummaryColumn extends TextColumn
{
    /**
     * @var TextRankFacade
     */
    private $textRank;

    /**
     * SummaryColumn constructor.
     *
     * @param TextRankFacade $textRank
     */
    public function __construct(TextRankFacade $textRank)
    {
        $this->textRank = $textRank;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($value): string
    {
        return parent::normalize(implode(' ', $this->textRank->summarizeTextBasic($value)));
    }

}
