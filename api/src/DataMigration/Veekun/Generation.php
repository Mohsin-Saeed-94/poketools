<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Generation migration
 *
 * @DataMigration(
 *     name="Generation",
 *     group="Veekun",
 *     source="veekun",
 *     destination="csv:///%kernel.project_dir%/resources/data/generation.csv",
 *     sourceIds={@IdField(name="id")},
 *     destinationIds={@IdField(name="id")}
 * )
 */
class Generation extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT
    "generations"."id",
    "generation_names"."name",
    "regions"."identifier" AS "main_region"
FROM "generations"
    JOIN "generation_names" ON "generations"."id" = "generation_names"."generation_id"
    JOIN "regions" ON "generations"."main_region_id" = "regions"."id"
WHERE "local_language_id" = 9;
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT
    count(*)
FROM "generations"
SQL
        );
        $sourceDriver->setCountStatement($countStatement);
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
