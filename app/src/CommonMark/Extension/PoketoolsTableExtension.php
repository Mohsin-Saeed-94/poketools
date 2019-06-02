<?php
/**
 * @file PoketoolsTableExtension.php
 */

namespace App\CommonMark\Extension;


use App\CommonMark\Block\Renderer\TableRenderer;
use Webuni\CommonMark\TableExtension\TableExtension;

/**
 * Extend the TableExtension to support special rendering
 */
class PoketoolsTableExtension extends TableExtension
{
    public function getBlockRenderers()
    {
        $renderers = parent::getBlockRenderers();

        foreach ($renderers as &$renderer) {
            if ($renderer instanceof \Webuni\CommonMark\TableExtension\TableRenderer) {
                $renderer = new TableRenderer();
            }
        }

        return $renderers;
    }
}
