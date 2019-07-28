<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\data\CsvParserTrait;
use App\Tests\data\DataFinderTrait;
use App\Tests\data\YamlParserTrait;
use Opis\JsonSchema\IFilter;

/**
 * Ensures that a given Type appears in a given Version (Group).
 *
 * It is assumed that the given entity and version (group) exist.
 *
 * Pass either version OR versionGroup
 *
 * Args:
 * - version: Version identifier
 * - versionGroup: Version group identifier
 */
class TypeInVersionGroup implements IFilter
{
    use CsvParserTrait;
    use YamlParserTrait;
    use DataFinderTrait;

    /**
     * @var array
     */
    private $typeVersionGroups;

    /**
     * EntityHasVersionGroup constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
        $this->typeVersionGroups = $this->getVersionGroupTypes();
    }

    /**
     * @return array
     */
    private function getVersionGroupTypes(): array
    {
        $finder = $this->getFinderForDirectory('type_chart');
        $finder->name('*.yaml');

        $types = [];
        foreach ($finder as $fileInfo) {
            $data = $this->parseYaml($fileInfo->getContents());
            foreach ($data['version_groups'] as $versionGroup) {
                $types[$versionGroup] = array_fill_keys(array_keys($data['efficacy']), 0);
            }
        }

        return $types;
    }

    /**
     * @param $data
     * @param array $args
     *
     * @return bool
     */
    public function validate($data, array $args): bool
    {
        if (isset($args['version'])) {
            $versionGroup = $this->getVersions()[$args['version']];
        } else {
            $versionGroup = $args['versionGroup'];
        }

        return isset($this->typeVersionGroups[$versionGroup][$data]);
    }

    /**
     * Get a map of versions to version groups
     *
     * @return string[]
     */
    private function getVersions(): array
    {
        static $versions = null;
        if (!isset($versions)) {
            $versionData = $this->getIteratorForCsv('version');
            $versions = array_column($versionData, 'version_group', 'identifier');
        }

        return $versions;
    }
}
