<?php


namespace App\Tests\dataschema\Filter;

use App\Tests\Traits\YamlParserTrait;
use Ds\Map;
use Ds\Set;
use Opis\JsonSchema\IFilter;

/**
 * Verify location area has the given shop
 *
 * It is assumed the entities described in the args exist.
 *
 * Args:
 * - versionGroup: version group identifier
 * - location: Location identifier
 * - area: Area identifier
 */
class LocationAreaHasShop implements IFilter
{

    use YamlParserTrait;

    /**
     * Map Location => version group => area => set of shops
     *
     * @var \Ds\Map
     */
    private Map $locationAreaShops;

    public function __construct()
    {
        $this->locationAreaShops = new Map();
    }

    /**
     * @inheritDoc
     */
    public function validate($data, array $args): bool
    {
        $versionGroup = $args['versionGroup'];
        $location = $args['location'];
        $area = $args['area'];

        if (!$this->locationAreaShops->hasKey($location)) {
            // Lookup data
            $entity = $this->loadEntityYaml(sprintf('location/%s', $location));
            $versionGroupAreaShops = new Map();
            foreach ($entity as $versionGroupSlug => $versionGroupData) {
                $areaShops = new Map();
                $this->addAreaShops($areaShops, $versionGroupData['areas']);
                $versionGroupAreaShops->put($versionGroupSlug, $areaShops);
            }
            $this->locationAreaShops->put($location, $versionGroupAreaShops);
        }

        return $this->locationAreaShops->get($location)
            ->get($versionGroup)
            ->get($area)
            ->contains($data);
    }

    private function addAreaShops(Map &$areaShops, array $data, array $parents = [])
    {
        foreach ($data as $areaIdentifier => $areaData) {
            $areaKey = array_merge($parents, [$areaIdentifier]);
            if (isset($areaData['shops'])) {
                $shops = new Set(array_keys($areaData['shops']));
            } else {
                $shops = new Set();
            }
            $areaShops->put(implode('/', $areaKey), $shops);
            if (isset($areaData['children'])) {
                $this->addAreaShops($areaShops, $areaData['children'], $areaKey);
            }
        }
    }

}
