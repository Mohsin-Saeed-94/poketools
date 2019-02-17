<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * BattleStyle migration.
 *
 * @DataMigration(
 *     name="BattleStyle",
 *     source="csv:///%kernel.project_dir%/resources/data/battle_style.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/BattleStyle",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class BattleStyle extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\BattleStyle $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\BattleStyle();
    }
}
