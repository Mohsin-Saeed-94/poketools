<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Region migration
 *
 * @DataMigration(
 *     name="Region",
 *     group="Veekun",
 *     source="veekun",
 *     destination="csv:///%kernel.project_dir%/resources/data/region.csv",
 *     sourceIds={@IdField(name="id")},
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Region extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "regions"."id",
       "regions"."identifier",
       "region_names"."name"
FROM "regions"
     JOIN "region_names" ON "region_names"."region_id" = "regions"."id"
WHERE "region_names"."local_language_id" = 9;
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT
    count(*)
FROM "regions"
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
