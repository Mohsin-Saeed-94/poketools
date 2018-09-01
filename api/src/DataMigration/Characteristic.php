<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Characteristic migration.
 *
 * @DataMigration(
 *     name="Characteristic",
 *     source="csv:///%kernel.project_dir%/resources/data/characteristic.csv",
 *     sourceIds={@IdField(name="iv_determinator"), @IdField(name="stat", type="string")},
 *     destination="doctrine:///App/Entity/Characteristic",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\Stat"}
 * )
 */
class Characteristic extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\Characteristic $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['stat'] = $this->referenceStore->get(Stat::class, ['identifier' => $sourceData['stat']]);

        $properties = array_keys($sourceData);
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\Characteristic();
    }
}
