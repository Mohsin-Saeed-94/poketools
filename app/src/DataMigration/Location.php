<?php

namespace App\DataMigration;

use App\Entity\LocationArea;
use App\Entity\LocationInVersionGroup;
use App\Entity\LocationMap;
use App\Entity\Media\RegionMap;
use App\Entity\Shop;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Location migration.
 *
 * @DataMigration(
 *     name="Location",
 *     source="/%kernel.project_dir%/resources/data/location",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\Location",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
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
            $versionGroupDestination = $this->transformVersionGroup($versionGroupSource, $versionGroupDestination);
            $versionGroupDestination->setSlug($identifier);
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
        if (!isset($sourceData['description'])) {
            $sourceData['description'] = null;
        }
        $properties = [
            'version_group',
            'region',
            'name',
            'description',
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
                $area->setSlug($areaIdentifier);
            }

            $area = $this->transformArea($areaData, $area);
            $destinationData->addArea($area);
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
            if (isset($sourceData['map']['z'])) {
                $map->setZIndex($sourceData['map']['z']);
            } else {
                $map->setZIndex(0);
            }
            $destinationData->setMap($map);
        } else {
            $destinationData->setMap(null);
        }

        return $destinationData;
    }

    /**
     * @param array $sourceData
     * @param LocationArea $destinationData
     *
     * @return LocationArea
     */
    private function transformArea(array $sourceData, LocationArea $destinationData): LocationArea
    {
        if (!isset($sourceData['default'])) {
            $sourceData['default'] = false;
        }

        /** @var LocationArea $destinationData */
        $properties = [
            'name',
            'position',
            'default',
        ];
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);

        // Shops
        if (isset($sourceData['shops'])) {
            foreach ($sourceData['shops'] as $shopIdentifier => $shopData) {
                $shop = $destinationData->getShops()->filter(
                    function (Shop $shop) use ($shopIdentifier) {
                        return ($shop->getSlug() === $shopIdentifier);
                    }
                );
                if (!$shop->isEmpty()) {
                    $shop = $shop->first();
                    $destinationData->removeShop($shop);
                } else {
                    $shop = new Shop();
                    $shop->setSlug($shopIdentifier);
                }

                /** @var Shop $shop */
                $shop = $this->mergeProperties($shopData, $shop);
                $destinationData->addShop($shop);
            }
        }

        // Children
        if (isset($sourceData['children'])) {
            $childPosition = 1;
            foreach ($sourceData['children'] as $childIdentifier => $childData) {
                $childData['position'] = $childPosition;
                $childPosition++;
                $child = $destinationData->getTreeChildren()->filter(
                    function (LocationArea $area) use ($childIdentifier) {
                        return ($area->getSlug() === $childIdentifier);
                    }
                );
                if (!$child->isEmpty()) {
                    $child = $child->first();
                    $destinationData->removeTreeChild($child);
                } else {
                    $child = new LocationArea();
                    $child->setSlug($childIdentifier);
                }

                $child = $this->transformArea($childData, $child);
                $destinationData->addTreeChild($child);
            }
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
