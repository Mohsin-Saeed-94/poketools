<?php

namespace App\DataMigration;

use App\Entity\LocationArea;
use App\Entity\LocationInVersionGroup;
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
        unset($sourceData['identifier']);
        foreach ($sourceData as $versionGroup => $versionGroupSource) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(VersionGroup::class, ['identifier' => $versionGroup]);
            $versionGroupSource['version_group'] = $versionGroup;
            $versionGroupDestination = $destinationData->findChildByGrouping($versionGroup) ?? (new LocationInVersionGroup());
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $destinationData->addChild($versionGroupDestination);
        }

        return $destinationData;
    }

    /**
     * @param array                              $sourceData
     * @param \App\Entity\LocationInVersionGroup $destinationData
     *
     * @return LocationInVersionGroup
     */
    protected function transformVersionGroup($sourceData, $destinationData)
    {
        $sourceData['region'] = $this->referenceStore->get(Region::class, ['identifier' => $sourceData['region']]);
        $properties = [
            'version_group',
            'region',
            'name',
        ];
        /** @var LocationInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);
        foreach ($sourceData['areas'] as $areaIdentifier => $areaData) {
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
