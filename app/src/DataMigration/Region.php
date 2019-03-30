<?php

namespace App\DataMigration;

use App\Entity\Media\RegionMap;
use App\Entity\RegionInVersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Region migration.
 *
 * @DataMigration(
 *     name="Region",
 *     source="yaml:///%kernel.project_dir%/resources/data/region",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/Region",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\VersionGroup"}
 * )
 */
class Region extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param $destinationData \App\Entity\Region
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        foreach ($sourceData as $versionGroup => $versionGroupSource) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupSource['version_group'] = $versionGroup;
            $versionGroupDestination = $destinationData->findChildByGrouping(
                    $versionGroup
                ) ?? (new RegionInVersionGroup());
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $destinationData->addChild($versionGroupDestination);
        }

        return $destinationData;
    }

    /**
     * @param $sourceData
     * @param RegionInVersionGroup $destinationData
     *
     * @return RegionInVersionGroup
     */
    protected function transformVersionGroup($sourceData, RegionInVersionGroup $destinationData): RegionInVersionGroup
    {
        // Maps
        $mapPosition = 1;
        foreach ($sourceData['maps'] as $mapSlug => &$mapData) {
            $mapData['position'] = $mapPosition;
            $mapPosition++;
            $map = null;
            foreach ($destinationData->getMaps() as $checkMap) {
                if ($checkMap->getSlug() === $mapSlug) {
                    $map = $checkMap;
                    break;
                }
            }
            if ($map === null) {
                $map = new RegionMap();
                $map->setSlug($mapSlug);
            }
            $map = $this->mergeProperties($mapData, $map);
            $mapData = $map;
        }
        unset($mapData);

        /** @var RegionInVersionGroup $region */
        $region = $this->mergeProperties($sourceData, $destinationData);

        return $region;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Region();
    }
}
