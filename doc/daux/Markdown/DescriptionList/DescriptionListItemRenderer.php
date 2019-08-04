<?php


namespace Todaymade\Daux\Extension\Markdown\DescriptionList;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Util\Xml;

class DescriptionListItemRenderer implements BlockRendererInterface
{

    /**
     * @param DescriptionListItem $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool $inTightList
     *
     * @return HtmlElement|string
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof DescriptionListItem)) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        /** @var AbstractBlock[] $children */
        $children = $block->children();
        $contents = $htmlRenderer->renderBlocks($children, $inTightList);
        if (substr($contents, 0, 1) === '<') {
            $contents = "\n".$contents;
        }
        if (substr($contents, -1, 1) === '>') {
            $contents .= "\n";
        }

        $attrs = [];
        foreach ($block->getData('attributes', []) as $key => $value) {
            $attrs[$key] = Xml::escape($value);
        }

        $ret = [
            new HtmlElement('dt', [], $block->getTerm()),
            new HtmlElement('dd', $attrs, $contents),
        ];

        return implode($htmlRenderer->getOption('inner_separator', "\n"), $ret);
    }
}
