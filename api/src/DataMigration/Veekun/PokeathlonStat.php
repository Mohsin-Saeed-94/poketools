<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Pokeathlon Stat migration.
 *
 * @DataMigration(
 *     name="Pokeathlon Stat",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/pokeathlon_stat.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class PokeathlonStat extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "pokeathlon_stats"."id",
       "pokeathlon_stats"."identifier",
       "pokeathlon_stat_names"."name"
FROM "pokeathlon_stats"
     JOIN "pokeathlon_stat_names" ON "pokeathlon_stats"."id" = "pokeathlon_stat_names"."pokeathlon_stat_id"
WHERE "pokeathlon_stat_names"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "pokeathlon_stats";
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
