<?php
/**
 * @file TableRenderer.php
 */

namespace App\CommonMark\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Ext\Table\TableRenderer as ParentTableRenderer;

/**
 * Add required styling to all tables
 */
class TableRenderer implements BlockRendererInterface
{
    /**
     * @var ParentTableRenderer
     */
    private $tableRenderer;

    /**
     * TableRenderer constructor.
     *
     * @param ParentTableRenderer $tableRenderer
     */
    public function __construct(ParentTableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    /**
     * {@inheritDoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        $element = $this->tableRenderer->render($block, $htmlRenderer, $inTightList);
        $element->setAttribute('class', 'table table-sm');

        return $element;
    }
}
