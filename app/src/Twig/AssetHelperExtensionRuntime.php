<?php
/**
 * @file AssetHelperExtensionRuntime.php
 */

namespace App\Twig;


use Twig\Extension\RuntimeExtensionInterface;

/**
 * Runtime for AssetHelperExtension
 */
class AssetHelperExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var string
     */
    protected $projectDir;

    /**
     * AssetHelperExtensionRuntime constructor.
     *
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Returns the contents of the given file.
     *
     * @param string $path
     *   The path to the file, relative to the public directory.
     *
     * @return string
     */
    public function assetInline(string $path): string
    {
        $path = $this->projectDir.'/public/'.$path;

        if (is_readable($path)) {
            return file_get_contents($path);
        }

        return '';
    }
}