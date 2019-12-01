<?php
/**
 * @file CallableBlock.php
 */

namespace App\CommonMark\Block\Element;


use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Cursor;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Element that stores information to render a callable.
 */
class CallableBlock extends AbstractBlock
{
    /**
     * @var ControllerReference
     */
    protected $fragment;

    /**
     * CallableElement constructor.
     *
     * @param ControllerReference $fragment
     */
    public function __construct(ControllerReference $fragment)
    {
        $this->fragment = $fragment;
    }

    /**
     * Returns true if this block can contain the given block as a child node
     *
     * @param AbstractBlock $block
     *
     * @return bool
     */
    public function canContain(AbstractBlock $block): bool
    {
        return false;
    }

    /**
     * Whether this is a code block
     *
     * @return bool
     */
    public function isCode(): bool
    {
        return true;
    }

    /**
     * @param Cursor $cursor
     *
     * @return bool
     */
    public function matchesNextLine(Cursor $cursor): bool
    {
        if ($cursor->isBlank()) {
            return false;
        }

        return true;
    }

    /**
     * @return ControllerReference
     */
    public function getFragment(): ControllerReference
    {
        return $this->fragment;
    }

}
