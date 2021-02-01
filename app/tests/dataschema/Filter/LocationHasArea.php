<?php


namespace App\Tests\dataschema\Filter;


use App\Tests\Traits\VersionVersionGroupTrait;
use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Verify a given area appears in a location.
 *
 * It is assumed the location exists.
 *
 * Pass either version *OR* versionGroup.
 *
 * Args:
 * - version: Version identifier
 * - versionGroup: Version group identifier
 * - location: Location identifier
 */
class LocationHasArea implements IFilter
{

    use YamlParserTrait;
    use VersionVersionGroupTrait;

    /**
     * Map locations => verison groups => set of their areas
     *
     * @var \Ds\Map
     */
    private Map $locationAreas;

    public function __construct()
    {
        $this->locationAreas = new Map();
    }

    /**
     * @inheritDoc
     */
    public function validate($data, array $args): bool
    {
        if (isset($args['version'])) {
            $versionGroup = $this->getVersionVersionGroup($args['version']);
        } else {
            $versionGroup = $args['versionGroup'];
        }
        $location = $args['location'];
        if (!$this->locationAreas->hasKey($location)) {
            // Lookup data
            $entity = $this->loadEntityYaml(sprintf('location/%s', $location));
            $versionGroupAreas = new Map();
            foreach ($entity as $versionGroupSlug => $versionGroupData) {
                $areas = new Set();
                $this->addAreas($areas, $versionGroupData['areas']);
                $versionGroupAreas->put($versionGroupSlug, $areas);
            }
            $this->locationAreas->put($location, $versionGroupAreas);
        }

        return $this->locationAreas->get($location)
            ->get($versionGroup)
            ->contains($data);
    }

    protected function addAreas(Set &$areas, array $data, array $parents = [])
    {
        foreach ($data as $areaIdentifier => $areaData) {
            $areaKey = array_merge($parents, [$areaIdentifier]);
            $areas->add(implode('/', $areaKey));
            if (isset($areaData['children'])) {
                $this->addAreas($areas, $areaData['children'], $areaKey);
            }
        }
    }

}
