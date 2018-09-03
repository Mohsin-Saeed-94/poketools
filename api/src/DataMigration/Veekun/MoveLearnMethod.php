<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Move Learn Method migration.
 *
 * @DataMigration(
 *     name="Move Learn Method",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/move_learn_method",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class MoveLearnMethod extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "pokemon_move_methods"."id",
       "pokemon_move_methods"."identifier",
       "pokemon_move_methods"."id" AS "sort",
       "pokemon_move_method_prose"."name",
       "pokemon_move_method_prose"."description",
       group_concat("version_groups"."order" || ':' || "version_groups"."identifier") AS "version_groups"
FROM "pokemon_move_methods"
     JOIN "pokemon_move_method_prose"
         ON "pokemon_move_methods"."id" = "pokemon_move_method_prose"."pokemon_move_method_id"
     LEFT OUTER JOIN "version_group_pokemon_move_methods"
         ON "pokemon_move_methods"."id" = "version_group_pokemon_move_methods"."pokemon_move_method_id"
     LEFT OUTER JOIN "version_groups"
         ON "version_group_pokemon_move_methods"."version_group_id" = "version_groups"."id"
WHERE "pokemon_move_method_prose"."local_language_id" = 9
GROUP BY "pokemon_move_methods"."id"
ORDER BY "sort";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "pokemon_move_methods";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $keys = [
            'identifier',
            'sort',
            'name',
            'description',
        ];
        foreach ($keys as $key) {
            if (!isset($destinationData[$key])) {
                $destinationData[$key] = $sourceData[$key];
            }
        }
        if (!isset($destinationData['version_groups'])) {
            $versionGroups = [];
            if (!empty($sourceData['version_groups'])) {
                foreach (explode(',', $sourceData['version_groups']) as $versionGroupString) {
                    $versionGroupInfo = explode(':', $versionGroupString);
                    $sort = $versionGroupInfo[0];
                    $versionGroup = $versionGroupInfo[1];
                    $versionGroups[$sort] = $versionGroup;
                }
                ksort($versionGroups);
            }
            $destinationData['version_groups'] = array_values($versionGroups);
        }

        return $destinationData;
    }
}
