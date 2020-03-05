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
 *     source="/%kernel.project_dir%/resources/data/super_contest_effect.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="id")},
 *     destination="\App\Entity\SuperContestEffect",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
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
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);

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
