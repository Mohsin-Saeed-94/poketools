<?php

namespace App\DataMigration;

use App\Entity\LocationArea;
use App\Entity\LocationInVersionGroup;
use App\Entity\LocationMap;
use App\Entity\Media\RegionMap;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Location migration.
 *
 * @DataMigration(
 *     name="Location",
 *     source="yaml:///%kernel.project_dir%/resources/data/location",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Location",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\VersionGroup", "App\DataMigration\Region"}
 * )
 */
class Location extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $identifier = $sourceData['identifier'];
        unset($sourceData['identifier']);
        foreach ($sourceData as $versionGroup => $versionGroupSource) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupSource['version_group'] = $versionGroup;
            $versionGroupDestination = $destinationData->findChildByGrouping(
                    $versionGroup
                ) ?? (new LocationInVersionGroup());
            $versionGroupDestination->setSlug($identifier);
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $destinationData->addChild($versionGroupDestination);
        }

        return $destinationData;
    }

    /**
     * @param array $sourceData
     * @param \App\Entity\LocationInVersionGroup $destinationData
     *
     * @return LocationInVersionGroup
     */
    protected function transformVersionGroup($sourceData, $destinationData)
    {
        $versionGroup = $sourceData['version_group'];
        /** @var \App\Entity\Region $region */
        $region = $this->referenceStore->get(Region::class, ['identifier' => $sourceData['region']]);
        $sourceData['region'] = $region->findChildByGrouping($versionGroup);
        $properties = [
            'version_group',
            'region',
            'name',
        ];
        /** @var LocationInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);

        // Areas
        $areaPosition = 1;
        foreach ($sourceData['areas'] as $areaIdentifier => $areaData) {
            $areaData['position'] = $areaPosition;
            $areaPosition++;
            $area = $destinationData->getAreas()->filter(
                function (LocationArea $area) use ($areaIdentifier) {
                    return ($area->getSlug() === $areaIdentifier);
                }
            );
            if (!$area->isEmpty()) {
                $area = $area->first();
                $destinationData->removeArea($area);
            } else {
                $area = new LocationArea();
            }

            $area->setSlug($areaIdentifier);
            if (!isset($areaData['default'])) {
                $areaData['default'] = false;
            }

            // Map
            if (isset($sourceData['map'])) {
                $map = $destinationData->getMap() ?? new LocationMap();
                $regionMap = $sourceData['region']->getMaps()->filter(
                    function (RegionMap $regionMap) use ($sourceData) {
                        return $regionMap->getSlug() === $sourceData['map']['map'];
                    }
                )->first();
                $map->setMap($regionMap);
                $map->setOverlay($sourceData['map']['overlay']);
                $destinationData->setMap($map);
            } else {
                $destinationData->setMap(null);
            }

            /** @var LocationArea $area */
            $area = $this->mergeProperties($areaData, $area);
            $destinationData->addArea($area);
        }

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Location();
    }
}
