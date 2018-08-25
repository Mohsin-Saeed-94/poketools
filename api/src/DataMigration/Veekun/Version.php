<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Version migration.
 *
 * @DataMigration(
 *     name="Version",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/version.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Version extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "versions"."id",
       "versions"."identifier",
       "version_names"."name"
FROM "versions"
     JOIN "version_names"
         ON "versions"."id" = "version_names"."version_id"
     JOIN "version_groups"
         ON "versions"."version_group_id" = "version_groups"."id"
WHERE "version_names"."local_language_id" = 9
ORDER BY "version_groups"."order", "versions"."id";
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "versions";
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

        // The versions are ordered in the query; set the order based on that
        // order.
        static $order = 1;
        $destinationData['order'] = $order;
        $order++;

        return $destinationData;
    }
}
