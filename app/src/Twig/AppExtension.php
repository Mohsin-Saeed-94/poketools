<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * App Twig Extension
 */
class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('version_list', [AppExtensionRuntime::class, 'versionList']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'type_emblem', [AppExtensionRuntime::class, 'typeEmblem'], [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
