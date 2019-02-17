<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Evolution Trigger migration.
 *
 * @DataMigration(
 *     name="Evolution Trigger",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/evolution_trigger.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class EvolutionTrigger extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "evolution_triggers"."id",
       "evolution_triggers"."identifier",
       "evolution_trigger_prose"."name"
FROM "evolution_triggers"
     JOIN "evolution_trigger_prose"
         ON "evolution_triggers"."id" = "evolution_trigger_prose"."evolution_trigger_id"
WHERE "evolution_trigger_prose"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "evolution_triggers";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
