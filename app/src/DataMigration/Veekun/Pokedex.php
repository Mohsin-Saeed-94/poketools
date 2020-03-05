<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokedex migration.
 *
 * @DataMigration(
 *     name="Pokedex",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/pokedex",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Pokedex extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "pokedexes"."id",
       "pokedexes"."identifier",
       "pokedex_prose"."name",
       coalesce(group_concat(DISTINCT "version_groups"."identifier"),
                group_concat(DISTINCT "all_version_groups"."identifier")) AS "version_groups",
       "pokedex_prose"."description"
FROM "pokedexes"
     JOIN (
              SELECT "version_groups"."identifier"
              FROM "version_groups"
              ORDER BY "version_groups"."order"
          ) "all_version_groups"
     JOIN "pokedex_prose"
         ON "pokedexes"."id" = "pokedex_prose"."pokedex_id"
     LEFT JOIN "regions"
         ON "pokedexes"."region_id" = "regions"."id"
     LEFT JOIN "pokedex_version_groups"
         ON "pokedexes"."id" = "pokedex_version_groups"."pokedex_id"
     LEFT JOIN "version_groups"
         ON "pokedex_version_groups"."version_group_id" = "version_groups"."id"
WHERE "is_main_series" = 1
  AND "pokedex_prose"."local_language_id" = 9
GROUP BY "pokedexes"."id"
ORDER BY "pokedexes"."id", "version_groups"."order";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "pokedexes"
WHERE "is_main_series" = 1;
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $sourceData['version_groups'] = explode(',', $sourceData['version_groups']);
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
