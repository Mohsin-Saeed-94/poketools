<?php
/**
 * @file CommonMarkExtensionRuntime.php
 */

namespace App\Twig;


use League\CommonMark\CommonMarkConverter;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Runtime for \App\Twig\CommonMarkExtension
 */
class CommonMarkExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var CommonMarkConverter
     */
    private $standardMarkdown;

    /**
     * @var CommonMarkConverter
     */
    private $inlineMarkdown;

    public function __construct(CommonMarkConverter $standardMarkdown, CommonMarkConverter $inlineMarkdown)
    {
        $this->standardMarkdown = $standardMarkdown;
        $this->inlineMarkdown = $inlineMarkdown;
    }

    /**
     * @param string $value
     * @param bool $allowBlocks
     *   Parse block-level Markdown tags
     *
     * @return string
     */
    public function markdown(string $value, bool $allowBlocks = true)
    {
        if ($allowBlocks) {
            $markdown = $this->standardMarkdown;
        } else {
            $markdown = $this->inlineMarkdown;
        }
        $html = trim($markdown->convertToHtml($value));

        return $html;
    }
}
