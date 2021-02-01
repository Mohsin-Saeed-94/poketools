<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\VersionVersionGroupTrait;
use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
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

    use YamlParserTrait;
    use VersionVersionGroupTrait;

    /**
     * Map types to a set of version groups
     *
     * @var Map
     */
    private Map $versionGroupTypes;


    public function __construct()
    {
        $this->versionGroupTypes = new Map();

        // Build the map
        foreach ($this->buildYamlDataProvider('type_chart') as $data) {
            $data = $data[0];
            $versionGroups = $data['version_groups'];
            $types = new Set(array_keys($data['efficacy']));
            foreach ($versionGroups as $versionGroup) {
                $this->versionGroupTypes->put($versionGroup, $types);
            }
        }
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

        return $this->versionGroupTypes->get($versionGroup)->contains($data);
    }

}
