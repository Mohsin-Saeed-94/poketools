<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Pal Park Area migration.
 *
 * @DataMigration(
 *     name="Pal Park Area",
 *     source="csv:///%kernel.project_dir%/resources/data/pal_park_area.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/PalParkArea",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class PalParkArea extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        $properties = array_keys($sourceData);
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\PalParkArea();
    }
}
