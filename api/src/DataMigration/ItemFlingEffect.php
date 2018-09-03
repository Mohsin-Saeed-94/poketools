<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Item Fling Effect migration.
 *
 * @DataMigration(
 *     name="Item Fling Effect",
 *     source="csv:///%kernel.project_dir%/resources/data/item_fling_effect.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/ItemFlingEffect",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class ItemFlingEffect extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\ItemFlingEffect $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\ItemFlingEffect();
    }
}
