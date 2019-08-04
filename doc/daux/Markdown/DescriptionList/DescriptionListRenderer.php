<?php


namespace Todaymade\Daux\Extension\Markdown\DescriptionList;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Util\Xml;

/**
 * Class DescriptionListRenderer
 */
class DescriptionListRenderer implements BlockRendererInterface
{

    /**
     * @param DescriptionListBlock $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool $inTightList
     *
     * @return HtmlElement|string
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof DescriptionListBlock)) {
            throw new \InvalidArgumentException('Incompatible block type: '.get_class($block));
        }

        $attrs = [];
        foreach ($block->getData('attributes', []) as $key => $value) {
            $attrs[$key] = Xml::escape($value);
        }

        /** @var AbstractBlock[] $children */
        $children = $block->children();

        return new HtmlElement(
            'dl',
            $attrs,
            $htmlRenderer->getOption('inner_separator', "\n").$htmlRenderer->renderBlocks(
                $children,
                $block->isTight()
            ).$htmlRenderer->getOption('inner_separator', "\n")
        );
    }
}
