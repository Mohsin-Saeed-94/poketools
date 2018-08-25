<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Super Contest Effect migration.
 *
 * @DataMigration(
 *     name="Super Contest Effect",
 *     source="csv:///%kernel.project_dir%/resources/data/super_contest_effect.csv",
 *     sourceIds={@IdField(name="id")},
 *     destination="doctrine:///App/Entity/SuperContestEffect",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class SuperContestEffect extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $properties = [
            'flavor_text',
            'appeal',
        ];
        $destinationData = $this->mergeProperties($properties, $sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\SuperContestEffect();
    }
}
