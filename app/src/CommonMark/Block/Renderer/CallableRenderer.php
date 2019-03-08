<?php
/**
 * @file CallableRenderer.php
 */

namespace App\CommonMark\Block\Renderer;


use App\CommonMark\Block\Element\CallableBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Renderer for CallableBlock.
 */
class CallableRenderer implements BlockRendererInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var FragmentHandler
     */
    protected $fragmentHandler;

    /**
     * CallableRenderer constructor.
     *
     * @param LoggerInterface $logger
     * @param FragmentHandler $fragmentHandler
     */
    public function __construct(LoggerInterface $logger, FragmentHandler $fragmentHandler)
    {
        $this->logger = $logger;
        $this->fragmentHandler = $fragmentHandler;
    }

    /**
     * {@inheritdoc}
     *
     * @param CallableBlock $block
     */
    public function render($block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        try {
            $rendered = $this->fragmentHandler->render($block->getFragment());
        } catch (\Exception $e) {
            $this->logger->warning('Could not render "%s": '.$e->getMessage());
            $rendered = '';
        }

        return new HtmlElement('div', [], $rendered);
    }
}