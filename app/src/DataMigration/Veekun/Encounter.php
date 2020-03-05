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
 * Encounter migration.
 *
 * @DataMigration(
 *     name="Encounter",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/encounter.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class Encounter extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "encounters"."id",
       "versions"."identifier" AS "version",
       "locations"."identifier" AS "location",
       ifnull("location_areas"."identifier", 'whole-area') AS "area",
       "encounter_methods"."identifier" AS "method",
       "pokemon_species"."identifier" AS "species",
       "pokemon"."identifier" AS "pokemon",
       CASE
           WHEN "encounters"."min_level" = "encounters"."max_level" THEN
               "encounters"."min_level"
           ELSE
               "encounters"."min_level" || '-' || "encounters"."max_level"
       END AS "level",
       "encounter_slots"."rarity" AS "chance",
       group_concat(DISTINCT "encounter_conditions"."identifier" || '/' ||
                             "encounter_condition_values"."identifier") AS "conditions",
       NULL AS "note"
FROM "encounters"
     JOIN "encounter_slots"
          ON "encounters"."encounter_slot_id" = "encounter_slots"."id"
     JOIN "versions"
          ON "encounters"."version_id" = "versions"."id"
     JOIN "location_areas"
          ON "encounters"."location_area_id" = "location_areas"."id"
     JOIN "locations"
          ON "location_areas"."location_id" = "locations"."id"
     JOIN "pokemon"
          ON "encounters"."pokemon_id" = "pokemon"."id"
     JOIN "pokemon_species"
          ON "pokemon"."species_id" = "pokemon_species"."id"
     JOIN "encounter_methods"
          ON "encounter_slots"."encounter_method_id" = "encounter_methods"."id"
     LEFT OUTER JOIN "encounter_condition_value_map"
                     ON "encounters"."id" = "encounter_condition_value_map"."encounter_id"
     LEFT OUTER JOIN "encounter_condition_values"
                     ON "encounter_condition_value_map"."encounter_condition_value_id" =
                        "encounter_condition_values"."id"
     LEFT OUTER JOIN "encounter_conditions"
                     ON "encounter_condition_values"."encounter_condition_id" = "encounter_conditions"."id"
GROUP BY "encounters"."id";
SQL
        );
        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "encounters";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
