<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Add markdown support
 */
class CommonMarkExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [CommonMarkExtensionRuntime::class, 'markdown'], ['is_safe' => ['html']]),
        ];
    }
}
