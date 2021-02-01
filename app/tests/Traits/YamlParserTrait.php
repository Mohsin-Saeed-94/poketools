<?php


namespace App\Tests\Traits;


use Generator;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

trait YamlParserTrait
{

    use DataFinderTrait;

    /**
     * Build a data provider for a directory of YAML files.
     *
     * @param string $path
     *  Path relative to data directory
     *
     * @return Generator
     */
    protected function buildYamlDataProvider(string $path): Generator
    {
        $finder = $this->getFinderForDirectory($path)
            ->files()
            ->name(['*.yaml', '*.yml'])
            ->sort(
                fn(SplFileInfo $a, SplFileInfo $b) => strnatcasecmp(
                    $a->getRelativePathname(),
                    $b->getRelativePathname()
                )
            );
        foreach ($finder as $fileInfo) {
            $key = ltrim(
                implode(
                    '/',
                    [
                        $fileInfo->getRelativePath(),
                        $fileInfo->getFilenameWithoutExtension(),
                    ]
                ),
                '/'
            );
            if (is_numeric($key)) {
                $key = '#'.$key;
            }
            yield $key => [
                $this->getYamlParser()->parseFile($fileInfo->getPathname()),
            ];
        }
    }

    /**
     * @return Parser
     */
    private function getYamlParser(): Parser
    {
        static $parser = null;

        if (!isset($parser)) {
            $parser = new Parser();
        }

        return $parser;
    }

    /**
     * @param string $entity
     *
     * @return array
     */
    protected function loadEntityYaml(string $entity): array
    {
        $path = implode('/', [dirname(__FILE__, 3), 'resources/data', $entity]).'.yaml';

        return $this->getYamlParser()->parseFile($path);
    }

}
