<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Move Flag migration.
 *
 * @DataMigration(
 *     name="Move Flag",
 *     source="/%kernel.project_dir%/resources/data/move_flag.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\MoveFlag",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class MoveFlag extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param $destinationData \App\Entity\MoveFlag
     */
    public function transform($sourceData, $destinationData)
    {
        $destinationData->setSlug($sourceData['identifier']);
        unset($sourceData['identifier']);

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\MoveFlag();
    }
}
