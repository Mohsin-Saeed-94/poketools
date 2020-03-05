<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Region migration
 *
 * @DataMigration(
 *     name="Region",
 *     group="Veekun",
 *     source="veekun",
 *     destination="/%kernel.project_dir%/resources/data/region",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver",
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
        $sourceDriver->setStatement(
            <<<SQL
SELECT "regions"."id",
       "regions"."identifier",
       "region_names"."name",
       group_concat(DISTINCT "version_groups"."identifier") AS "version_groups"
FROM "regions"
       JOIN "region_names" ON "region_names"."region_id" = "regions"."id"
       JOIN "version_group_regions" ON "regions"."id" = "version_group_regions"."region_id"
       JOIN "version_groups" ON "version_group_regions"."version_group_id" = "version_groups"."id"
WHERE "region_names"."local_language_id" = 9
GROUP BY "regions"."id"
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT
    count(*)
FROM "regions"
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $destinationData['identifier'] = $sourceData['identifier'];
        unset($sourceData['identifier']);
        $versionGroups = explode(',', $sourceData['version_groups']);
        unset($sourceData['version_groups']);

        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        $versionGroupData = $sourceData;
        $sourceData = [];
        foreach ($versionGroups as $versionGroup) {
            $versionGroupRow = $versionGroupData;
            $versionGroupRow['maps'] = [];

            $sourceData[$versionGroup] = $versionGroupRow;
        }

        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     * @param YamlDestinationDriver $destinationDriver
     */
    public function configureDestination(DestinationDriverInterface $destinationDriver)
    {
        $destinationDriver->setOption('refs', true);
    }
}
