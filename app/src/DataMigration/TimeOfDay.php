<?php

namespace App\DataMigration;

use Cake\Chronos\Chronos;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Time of Day migration.
 *
 * @DataMigration(
 *     name="Time of Day",
 *     source="/%kernel.project_dir%/resources/data/time_of_day.csv",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\CsvSourceDriver",
 *     sourceIds={@IdField(name="generation"), @IdField(name="identifier", type="string")},
 *     destination="\App\Entity\TimeOfDay",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={"App\DataMigration\Generation"}
 * )
 */
class TimeOfDay extends AbstractDoctrineDataMigration implements DataMigrationInterface
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

        $sourceData['generation'] = $this->referenceStore->get(Generation::class, ['id' => $sourceData['generation']]);
        $sourceData['starts'] = Chronos::createFromFormat('!H:i:s', $sourceData['starts']);
        $sourceData['ends'] = Chronos::createFromFormat('!H:i:s', $sourceData['ends']);
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\TimeOfDay();
    }
}
