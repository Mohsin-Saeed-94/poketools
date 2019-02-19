<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * TextRank Extension
 *
 * Expose TextRank to Twig templates
 *
 * @see http://php.science/textrank/
 */
class TextRankExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('highlights', [TextRankExtensionRuntime::class, 'highlights']),
            new TwigFilter('keywords', [TextRankExtensionRuntime::class, 'keywords']),
            new TwigFilter('summarize', [TextRankExtensionRuntime::class, 'summarize']),
        ];
    }
}
