<?php
/**
 * @file PoketoolsTableExtension.php
 */

namespace App\CommonMark\Extension;


use App\CommonMark\Block\Renderer\TableRenderer;
use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;
use Webuni\CommonMark\TableExtension\Table;
use Webuni\CommonMark\TableExtension\TableExtension;

/**
 * Extend the TableExtension to support special rendering
 */
class PoketoolsTableExtension implements ExtensionInterface
{

    /**
     * @var TableRenderer
     */
    private $tableRenderer;

    /**
     * PoketoolsTableExtension constructor.
     *
     * @param TableRenderer $tableRenderer
     */
    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    /**
     * {@inheritDoc}
     */
    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment
            ->addBlockRenderer(Table::class, $this->tableRenderer, 200);
    }
}
