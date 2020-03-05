<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Stat migration.
 *
 * @DataMigration(
 *     name="Stat",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/stat.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Stat extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "stats"."id",
       "stats"."identifier",
       "move_damage_classes"."identifier" AS "damage_class",
       "stats"."is_battle_only" AS "battle_only",
       "stat_names"."name"
FROM "stats"
     JOIN "stat_names"
         ON "stats"."id" = "stat_names"."stat_id"
     LEFT OUTER JOIN "move_damage_classes"
         ON "stats"."damage_class_id" = "move_damage_classes"."id"
WHERE "stat_names"."local_language_id" = 9
ORDER BY coalesce("stats"."game_index", 65535), "stats"."id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "stats";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $sourceData['name'] = ucwords($sourceData['name']);
        $destinationData = array_merge($sourceData, $destinationData);
        $destinationData['battle_only'] = (int)$destinationData['battle_only'];

        return $destinationData;
    }
}
