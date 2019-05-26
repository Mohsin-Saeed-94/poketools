<?php
/**
 * @file GaugeExtension.php
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class GaugeExtension
 */
class GaugeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'gauge_linear', [GaugeExtensionRuntime::class, 'gaugeLinear'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'gauge_radial', [GaugeExtensionRuntime::class, 'gaugeRadial'], [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }
}
