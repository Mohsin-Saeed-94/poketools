<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Item Flag migration.
 *
 * @DataMigration(
 *     name="Item Flag",
 *     source="csv:///%kernel.project_dir%/resources/data/item_flag.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/ItemFlag",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class ItemFlag extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\ItemFlag();
    }
}
