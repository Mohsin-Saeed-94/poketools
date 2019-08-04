<?php


namespace Todaymade\Daux\Extension\Markdown\Schema;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;

/**
 * Class SchemaRenderer
 */
class SchemaRenderer implements BlockRendererInterface
{

    /**
     * @param SchemaBlock $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool $inTightList
     *
     * @return HtmlElement|string
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        $schemaString = file_get_contents($block->getSchemaPath());

        return new HtmlElement('pre', [], new HtmlElement('code', ['class' => 'code-json'], $schemaString));
    }
}
