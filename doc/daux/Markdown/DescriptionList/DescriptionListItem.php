<?php


namespace Todaymade\Daux\Extension\Markdown\DescriptionList;


use League\CommonMark\Block\Element\ListData;
use League\CommonMark\Block\Element\ListItem;

/**
 * Class DescriptionListItem
 */
class DescriptionListItem extends ListItem
{
    /**
     * @var string
     */
    private $term;

    /**
     * DescriptionListItem constructor.
     *
     * @param ListData $listData
     * @param string $term
     */
    public function __construct(ListData $listData, string $term)
    {
        parent::__construct($listData);
        $this->term = $term;
    }

    /**
     * @return string
     */
    public function getTerm(): string
    {
        return $this->term;
    }
}
