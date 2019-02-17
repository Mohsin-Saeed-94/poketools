<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Contest Effect migration.
 *
 * @DataMigration(
 *     name="Contest Effect",
 *     source="csv:///%kernel.project_dir%/resources/data/contest_effect.csv",
 *     sourceIds={@IdField(name="id")},
 *     destination="doctrine:///App/Entity/ContestEffect",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class ContestEffect extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\ContestEffect();
    }
}
