<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Characteristic migration.
 *
 * @DataMigration(
 *     name="Characteristic",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/characteristic.csv",
 *     destinationIds={@IdField(name="iv_determinator"), @IdField(name="stat", type="string")}
 * )
 */
class Characteristic extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "characteristics"."id",
       "stats"."identifier" AS "stat",
       "characteristics"."gene_mod_5" AS "iv_determinator",
       "characteristic_text"."message"
FROM "characteristics"
     JOIN "characteristic_text"
         ON "characteristics"."id" = "characteristic_text"."characteristic_id"
     JOIN "stats"
         ON "characteristics"."stat_id" = "stats"."id"
WHERE "characteristic_text"."local_language_id" = 9
ORDER BY "iv_determinator", "stats"."id";
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "characteristics";
SQL
        );
        $sourceDriver->setCountStatement($countStatement);
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
