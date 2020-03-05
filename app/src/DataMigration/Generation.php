<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Generation migration.
 *
 * @DataMigration(
 *     name="Generation",
 *     source="/%kernel.project_dir%/resources/data/generation.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="id")},
 *     destination="\App\Entity\Generation",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class Generation extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $sourceData['number'] = $sourceData['id'];
        $sourceData['position'] = $sourceData['id'];
        $properties = [
            'name',
            'number',
            'position',
        ];
        $this->mergeProperties($sourceData, $destinationData, $properties);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Generation();
    }
}
