<?php

namespace App\CommonMark\Extension;


use App\CommonMark\Block\Element\CallableBlock;
use App\CommonMark\Block\Parser\CallableParser;
use App\CommonMark\Block\Renderer\CallableRenderer;
use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * CommonMark Extension to manage special app-specific pieces (blocks only).
 */
class PoketoolsBlockExtension implements ExtensionInterface
{

    /**
     * @var CallableParser
     */
    private $callableParser;

    /**
     * @var CallableRenderer
     */
    private $callableRenderer;

    /**
     * PoketoolsCommonMarkExtension constructor.
     *
     * @param CallableParser $controllerParser
     * @param CallableRenderer $callableRenderer
     */
    public function __construct(
        CallableParser $controllerParser,
        CallableRenderer $callableRenderer
    ) {
        $this->callableParser = $controllerParser;
        $this->callableRenderer = $callableRenderer;
    }

    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment
            ->addBlockParser($this->callableParser)
            ->addBlockRenderer(CallableBlock::class, $this->callableRenderer);
    }
}
