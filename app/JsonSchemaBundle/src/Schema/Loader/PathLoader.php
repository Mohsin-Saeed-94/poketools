<?php
/**
 * @file PathLoader.php
 */

namespace DragoonBoots\JsonSchemaBundle\Schema\Loader;


use DragoonBoots\JsonSchemaBundle\Exception\UnknownUriPrefixException;
use Opis\JsonSchema\ISchemaLoader;
use Opis\JsonSchema\Schema;
use Symfony\Component\Finder\Finder;

/**
 * Load schema(s) from a given directory path.
 */
class PathLoader implements ISchemaLoader
{
    /**
     * Map URI Prefixes to directories
     *
     * @var string[]
     */
    protected $map = [];

    /**
     * Map URIs to loaded Schema objects.
     *
     * @var Schema[]
     */
    protected $loaded = [];

    /**
     * @inheritdoc
     */
    public function loadSchema(string $uri)
    {
        // Check if already loaded
        if (isset($this->loaded[$uri])) {
            return $this->loaded[$uri];
        }

        // Check the mapping
        foreach ($this->map as $prefix => $dir) {
            if (strpos($uri, $prefix) === 0) {
                // We have a match
                $path = substr($uri, strlen($prefix) + 1);
                $path = $dir.'/'.ltrim($path, '/');

                if (file_exists($path)) {
                    // Create a schema object
                    $schema = Schema::fromJsonString(file_get_contents($path));
                    // Save it for reuse
                    $this->loaded[$uri] = $schema;

                    return $schema;
                }
            }
        }

        // Nothing found
        return null;
    }

    /**
     * @param string $dir
     * @param string $uriPrefix
     *
     * @return bool
     */
    public function registerPath(string $dir, string $uriPrefix): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $uriPrefix = rtrim($uriPrefix, '/');
        $dir = rtrim($dir, '/');

        $this->map[$uriPrefix] = $dir;

        return true;
    }

    /**
     * Get a list of available schema uris
     *
     * @param string $uriPrefix
     *
     * @return string[]
     *   A list of Schema URIs available.
     */
    public function getAvailable(string $uriPrefix): array
    {
        if (!isset($this->map[$uriPrefix])) {
            throw new UnknownUriPrefixException($uriPrefix);
        }

        $available = [];
        $finder = new Finder();
        $finder->files()
            ->in($this->map[$uriPrefix])
            ->name('*.json')
            ->sortByName();

        foreach ($finder as $fileInfo) {
            // Build the URI from the relative path
            $uri = rtrim($uriPrefix, '/').'/'.ltrim($fileInfo->getRelativePathname(), '/');
            $available[] = $uri;
        }

        return $available;
    }
}
