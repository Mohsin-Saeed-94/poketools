<?php

namespace App\DataMigration;

use App\Entity\LocationArea;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Exception\MigrationException;

/**
 * Shop Item migration.
 *
 * @DataMigration(
 *     name="Shop Item",
 *     source="csv:///%kernel.project_dir%/resources/data/shop_item.csv",
 *     sourceIds={
 *       @IdField(name="version_group", type="string"),
 *       @IdField(name="location", type="string"),
 *       @IdField(name="area", type="string"),
 *       @IdField(name="shop", type="string"),
 *       @IdField(name="item", type="string")
 *     },
 *     destination="doctrine:///App/Entity/ShopItem",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *       "App\DataMigration\Location",
 *       "App\DataMigration\VersionGroup",
 *       "App\DataMigration\Item"
 *     }
 * )
 */
class ShopItem extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     *
     * @param \App\Entity\ShopItem|null $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        // Only need to do a ton of initialization on new shop items.
        if ($destinationData === null) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(
                VersionGroup::class,
                ['identifier' => $sourceData['version_group']]
            );
            /** @var \App\Entity\Location $location */
            $location = $this->referenceStore->get(Location::class, ['identifier' => $sourceData['location']]);
            $location = $location->findChildByGrouping($versionGroup);
            if (!isset($location)) {
                throw new MigrationException(
                    sprintf(
                        'Location %s" does not exist in Version Group "%s"',
                        $sourceData['location'],
                        $sourceData['version_group']
                    )
                );
            }
            /** @var \App\Entity\Item $item */
            $item = $this->referenceStore->get(Item::class, ['identifier' => $sourceData['item']]);
            $item = $item->findChildByGrouping($versionGroup);
            if (!isset($item)) {
                throw new MigrationException(
                    sprintf(
                        'Item %s" does not exist in Version Group "%s"',
                        $sourceData['item'],
                        $sourceData['version_group']
                    )
                );
            }

            // Find location area
            $locationArea = $this->findArea(explode('/', $sourceData['area']), $location->getAreas());
            if (!isset($locationArea)) {
                throw new MigrationException(
                    sprintf('Location "%s" has no area "%s".', $sourceData['location'], $sourceData['area'])
                );
            }

            // Find shop
            $shop = null;
            foreach ($locationArea->getShops() as $checkShop) {
                if ($checkShop->getSlug() === $sourceData['shop']) {
                    $shop = $checkShop;
                    break;
                }
            }
            if (!isset($shop)) {
                throw new MigrationException(
                    sprintf(
                        'Location "%s" area "%s" has no shop called "%s"',
                        $sourceData['location'],
                        $sourceData['area'],
                        $sourceData['shop']
                    )
                );
            }

            // Create the new shop item
            $destinationData = new \App\Entity\ShopItem();
            $destinationData->setShop($shop)
                ->setItem($item);
        }

        static $position = 1;
        $sourceData['position'] = $position;
        $position++;
        $properties = [
            'position',
            'buy',
        ];
        $this->mergeProperties($sourceData, $destinationData, $properties);

        return $destinationData;
    }

    /**
     * @param array $identifierParts
     * @param LocationArea[] $areas
     *
     * @return LocationArea|null
     */
    private function findArea(array $identifierParts, $areas): ?LocationArea
    {
        $searchAreaIdentifier = array_shift($identifierParts);
        foreach ($areas as $area) {
            if ($area->getSlug() === $searchAreaIdentifier) {
                if ($identifierParts) {
                    // Drill down further into the tree
                    return $this->findArea($identifierParts, $area->getTreeChildren());
                }

                return $area;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        // A lot of initialization needs to be performed on a default result
        // based on data in the row.  A null default will tell the transform to
        // perform this work.
        return null;
    }
}
