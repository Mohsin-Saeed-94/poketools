<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Move Ailment migration.
 *
 * @DataMigration(
 *     name="Move Ailment",
 *     source="csv:///%kernel.project_dir%/resources/data/move_ailment.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/MoveAilment",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class MoveAilment extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        $properties = array_keys($sourceData);
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\MoveAilment();
    }
}
