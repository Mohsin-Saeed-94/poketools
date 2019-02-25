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

    public function markdown(string $value)
    {
        return $this->markdown->convertToHtml($value);
    }
}
