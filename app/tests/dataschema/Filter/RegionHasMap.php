<?php


namespace App\Tests\dataschema\Filter;

use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Verify region has the given map
 *
 * Args:
 * - versionGroup: Version group identifier
 * - region: Region identifier
 */
class RegionHasMap implements IFilter
{

    use YamlParserTrait;

    /**
     * Map region => version group => set of maps
     *
     * @var Map
     */
    private Map $regionMaps;

    public function __construct()
    {
        $this->regionMaps = new Map();
    }

    /**
     * @inheritDoc
     */
    public function validate($data, array $args): bool
    {
        $versionGroup = $args['versionGroup'];
        $region = $args['region'];
        if (!$this->regionMaps->hasKey($region)) {
            // Lookup data
            $entity = $this->loadEntityYaml('region/'.$region);
            $versionGroupMaps = new Map();
            foreach ($entity as $versionGroupSlug => $versionGroupData) {
                $versionGroupMaps->put($versionGroupSlug, new Set(array_keys($versionGroupData['maps'] ?? [])));
            }
            $this->regionMaps->put($region, $versionGroupMaps);
        }

        return $this->regionMaps->get($region)
            ->get($versionGroup)
            ->contains($data);
    }

}
