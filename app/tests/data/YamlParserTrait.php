<?php


namespace App\Tests\data;


use Symfony\Component\Yaml\Parser;

/**
 * Trait YamlParserTrait
 */
trait YamlParserTrait
{
    /**
     * @param string $entity
     *
     * @return array
     */
    protected function loadEntityYaml(string $entity): array
    {
        $path = realpath(__DIR__.'/../../resources/data').'/'.ltrim($entity, '/').'.yaml';

        return $this->getDataFromYaml($path);
    }

    /**
     * Read a YAML file from the given path.
     *
     * @param string $filePath
     *
     * @return array
     */
    protected function getDataFromYaml(string $filePath): array
    {
        static $cache = [];
        if (!isset($cache[$filePath])) {
            $cache[$filePath] = $this->parseYaml(file_get_contents($filePath));
        }

        return $cache[$filePath];
    }

    /**
     * @param string $yaml
     *
     * @return array
     */
    protected function parseYaml(string $yaml): array
    {
        return $this->getYamlParser()->parse($yaml);
    }

    /**
     * @return Parser
     */
    protected function getYamlParser(): Parser
    {
        static $parser = null;

        if (!isset($parser)) {
            $parser = new Parser();
        }

        return $parser;
    }
}
