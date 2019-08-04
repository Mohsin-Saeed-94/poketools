<?php


namespace Todaymade\Daux\Extension\Markdown\DescriptionList;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\ListBlock;

/**
 * Class DescriptionListBlock
 */
class DescriptionListBlock extends ListBlock
{
    public const TYPE_DL = 'Description list';

    /**
     * @inheritDoc
     */
    public function canContain(AbstractBlock $block)
    {
        return $block instanceof DescriptionListItem;
    }
}
