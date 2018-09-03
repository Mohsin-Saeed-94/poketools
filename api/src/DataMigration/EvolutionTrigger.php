<?php

namespace App\DataMigration;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Evolution Trigger migration.
 *
 * @DataMigration(
 *     name="Evolution Trigger",
 *     source="csv:///%kernel.project_dir%/resources/data/evolution_trigger.csv",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/EvolutionTrigger",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class EvolutionTrigger extends AbstractDoctrineDataMigration implements DataMigrationInterface
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
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * @inheritDoc
     */
    public function defaultResult()
    {
        return new \App\Entity\EvolutionTrigger();
    }
}
