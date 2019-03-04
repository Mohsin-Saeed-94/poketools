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
    private $markdown;

    public function __construct(CommonMarkConverter $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * @param string $value
     * @param bool $paragraph
     *   Should the output be wrapped in `<p>` tags?
     *
     * @return string
     */
    public function markdown(string $value, bool $paragraph = true)
    {
        $html = trim($this->markdown->convertToHtml($value));
        if (!$paragraph) {
            $html = preg_replace('`(?:^<p>)|(?:</p>$)`', '', $html);
        }

        return $html;
    }
}
