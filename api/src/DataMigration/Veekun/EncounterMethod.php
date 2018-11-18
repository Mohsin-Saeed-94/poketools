<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Encounter Method migration.
 *
 * @DataMigration(
 *     name="Encounter Method",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/encounter_method.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class EncounterMethod extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "encounter_methods"."id",
       "encounter_methods"."identifier",
       "encounter_method_prose"."name"
FROM "encounter_methods"
     JOIN "encounter_method_prose"
          ON "encounter_methods"."id" = "encounter_method_prose"."encounter_method_id"
WHERE "encounter_method_prose"."local_language_id" = 9
ORDER BY "encounter_methods"."order";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "encounter_methods";
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
