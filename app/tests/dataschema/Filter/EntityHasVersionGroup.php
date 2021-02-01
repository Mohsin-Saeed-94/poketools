<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\VersionVersionGroupTrait;
use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
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

    use YamlParserTrait;
    use VersionVersionGroupTrait;

    /**
     * @var string
     */
    private $entityType;

    /**
     * Map entitys to their version groups
     *
     * @var \Ds\Map
     */
    private Map $entityVersionGroups;

    /**
     * EntityHasVersionGroup constructor.
     *
     * @param string $entityType
     */
    public function __construct(string $entityType)
    {
        $this->entityVersionGroups = new Map();
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
            $versionGroup = $this->getVersionVersionGroup($args['version']);
        } else {
            $versionGroup = $args['versionGroup'];
        }

        if (!$this->entityVersionGroups->hasKey($data)) {
            // Lookup data
            $entity = $this->loadEntityYaml($this->entityType.'/'.$data);
            $this->entityVersionGroups->put($data, new Set(array_keys($entity)));
        }

        return $this->entityVersionGroups->get($data)->contains($versionGroup);
    }

}
