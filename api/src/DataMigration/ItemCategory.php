<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Item Category migration.
 *
 * @DataMigration(
 *     name="Item Category",
 *     source="csv:///%kernel.project_dir%/resources/data/item_category.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/ItemCategory",
 *     destinationIds={@IdField(name="id")},
 *     flush=true
 * )
 */
class ItemCategory extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        if (!empty($sourceData['parent'])) {
            $sourceData['tree_parent'] = $this->referenceStore->get(ItemCategory::class, ['identifier' => $sourceData['parent']], true);
        } else {
            $sourceData['tree_parent'] = null;
        }
        unset($sourceData['parent']);

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\ItemCategory();
    }
}
