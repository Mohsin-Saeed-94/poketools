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
 *     destination="/%kernel.project_dir%/resources/data/version_group",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver",
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
        $sourceDriver->setStatement(
            <<<SQL
SELECT "version_groups"."id",
       replace(group_concat(DISTINCT "versions"."identifier"), ',', '-') AS "identifier",
       replace(group_concat(DISTINCT "version_names"."name"), ',', '/') AS "name",
       "version_groups"."order" AS "position",
       "version_groups"."generation_id" AS "generation"
FROM "version_groups"
     JOIN "versions"
         ON "version_groups"."id" = "versions"."version_group_id"
     JOIN "version_names"
         ON "versions"."id" = "version_names"."version_id"
WHERE "version_names"."local_language_id" = 9
GROUP BY "version_groups"."id"
ORDER BY "version_groups"."order";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "version_groups"
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

        // Force proper data types
        foreach (['position', 'generation'] as $key) {
            $destinationData[$key] = (int)$destinationData[$key];
        }

        if (!isset($destinationData['features'])) {
            $destinationData['features'] = [];
        }

        return $destinationData;
    }
}
