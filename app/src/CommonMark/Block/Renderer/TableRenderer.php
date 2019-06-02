<?php
/**
 * @file TableRenderer.php
 */

namespace App\CommonMark\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;

/**
 * Add required styling to all tables
 */
class TableRenderer extends \Webuni\CommonMark\TableExtension\TableRenderer
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        $element = parent::render($block, $htmlRenderer, $inTightList);
        $element->setAttribute('class', 'table table-sm');

        return $element;
    }
}
