<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Type migration.
 *
 * @DataMigration(
 *     name="Type",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/type.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Type extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "types"."id",
       "types"."identifier",
       "type_names"."name",
       "move_damage_classes"."identifier" AS "damage_class",
       ("types"."identifier" IN ('unknown')) AS "hidden"
FROM "types"
     JOIN "type_names"
         ON "types"."id" = "type_names"."type_id"
     LEFT OUTER JOIN "move_damage_classes"
         ON "types"."damage_class_id" = "move_damage_classes"."id"
     LEFT OUTER JOIN (
                         SELECT "type_id",
                                "game_index" AS "position"
                         FROM "type_game_indices"
                         GROUP BY "type_id"
                     ) "type_sort_order"
         ON "type_sort_order"."type_id" = "types"."id"
WHERE "type_names"."local_language_id" = 9
ORDER BY coalesce("type_sort_order"."position"+1, 9999);
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "types";
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
