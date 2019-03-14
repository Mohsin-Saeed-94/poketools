<?php
/**
 * @file AssetHelperExtension.php
 */

namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AssetHelperExtension
 */
class AssetHelperExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'asset_inline', [AssetHelperExtensionRuntime::class, 'assetInline']
            ),
        ];
    }

}