<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\data\CsvParserTrait;
use App\Tests\data\YamlParserTrait;
use Opis\JsonSchema\IFilter;

/**
 * Ensures that a given entity lists a version group in it's top-level keys.
 *
 * It is assumed that the given entity and version (group) exist.
 *
 * Pass either version OR versionGroup
 *
 * Args:
 * - version: Version identifier
 * - versionGroup: Version group identifier
 */
class EntityHasVersionGroup implements IFilter
{
    use CsvParserTrait;
    use YamlParserTrait;

    /**
     * @var string
     */
    private $entityType;

    private $versionGroups = [];

    /**
     * EntityHasVersionGroup constructor.
     *
     * @param string $entityType
     */
    public function __construct(string $entityType)
    {
        $this->entityType = $entityType;
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

        $identifier = $this->entityType.'/'.$data;
        if (!isset($this->versionGroups[$identifier])) {
            $entity = $this->loadEntityYaml($identifier);
            $this->versionGroups[$identifier] = array_fill_keys(array_keys($entity), 0);
        }

        return isset($this->versionGroups[$identifier][$versionGroup]);
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
