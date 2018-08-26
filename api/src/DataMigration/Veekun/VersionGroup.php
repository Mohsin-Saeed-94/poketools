<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Version Group migration.
 *
 * @DataMigration(
 *     name="VersionGroup",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/version_group",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class VersionGroup extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."id",
       replace(group_concat(DISTINCT "versions"."identifier"), ',', '-') AS "identifier",
       replace(group_concat(DISTINCT "version_names"."name"), ',', '/') AS "name",
       "version_groups"."order",
       "version_groups"."generation_id" AS "generation",
       group_concat(DISTINCT "regions"."identifier") AS "regions"
FROM "version_groups"
     JOIN "versions"
         ON "version_groups"."id" = "versions"."version_group_id"
     JOIN "version_names"
         ON "versions"."id" = "version_names"."version_id"
     LEFT OUTER JOIN "version_group_regions"
         ON "version_groups"."id" = "version_group_regions"."version_group_id"
     LEFT OUTER JOIN "regions"
         ON "version_group_regions"."region_id" = "regions"."id"
WHERE "version_names"."local_language_id" = 9
GROUP BY "version_groups"."id"
ORDER BY "version_groups"."order", "versions"."id", "regions"."id";
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "version_groups"
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

        // Split regions from query
        if (!isset($destinationData['regions'])) {
            if (isset($sourceData['regions'])) {
                $destinationData['regions'] = explode(',', $sourceData['regions']);
            } else {
                $destinationData['regions'] = [];
            }
        }
        unset($sourceData['regions']);

        $destinationData = array_merge($sourceData, $destinationData);

        // Force proper data types
        foreach (['order', 'generation'] as $key) {
            $destinationData[$key] = (int)$destinationData[$key];
        }

        if (!isset($destinationData['features'])) {
            $destinationData['features'] = [];
        }

        return $destinationData;
    }
}
