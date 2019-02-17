<?php

namespace App\DataMigration\Veekun;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Location migration.
 *
 * @DataMigration(
 *     name="Location",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/location",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Location extends AbstractDataMigration implements DataMigrationInterface
{

    protected const DEFAULT_AREA = [
        'identifier' => 'whole-area',
        'name' => 'Whole area',
        'default' => true,
    ];

    /**
     * @var Statement
     */
    protected $areasStatement;

    /**
     * @var Statement
     */
    protected $versionGroupsStatement;

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "locations"."id",
       "locations"."identifier",
       "regions"."id" AS "region_id",
       "regions"."identifier" AS "region",
       "location_names"."name",
       "location_names"."subtitle"
FROM "locations"
     JOIN "location_names"
         ON "locations"."id" = "location_names"."location_id"
     JOIN "regions"
         ON "locations"."region_id" = "regions"."id"
WHERE "location_names"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "locations";
SQL
        );

        $this->areasStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT coalesce("location_areas"."identifier", 'whole-area') AS "identifier",
       coalesce("location_area_prose"."name", 'Whole area') AS "name"
FROM "location_areas"
     JOIN "location_area_prose"
         ON "location_areas"."id" = "location_area_prose"."location_area_id"
WHERE "location_areas"."location_id" = :location
  AND "location_area_prose"."local_language_id" = 9
ORDER BY "location_areas"."game_index";
SQL
        );

        $this->versionGroupsStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier"
FROM "version_group_regions"
     JOIN "version_groups"
         ON "version_group_regions"."version_group_id" = "version_groups"."id"
WHERE "region_id" = :region
ORDER BY "version_groups"."order";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $locationId = $sourceData['id'];
        unset($sourceData['id']);

        $destinationData['identifier'] = $sourceData['identifier'];
        unset($sourceData['identifier']);

        $regionId = $sourceData['region_id'];
        unset($sourceData['region_id']);

        if ($sourceData['subtitle']) {
            $sourceData['name'] .= ' ('.$sourceData['subtitle'].')';
        }
        unset($sourceData['subtitle']);

        $this->areasStatement->execute(['location' => $locationId]);
        $this->areasStatement->setFetchMode(FetchMode::ASSOCIATIVE);
        $areas = $this->areasStatement->fetchAll();
        // Some locations have no area listed.
        if (empty($areas)) {
            $areas = [self::DEFAULT_AREA];
        }

        $this->versionGroupsStatement->execute(['region' => $regionId]);
        $this->versionGroupsStatement->setFetchMode(FetchMode::COLUMN, 0);

        foreach ($this->versionGroupsStatement as $versionGroup) {
            $versionGroupData = $sourceData;
            $defaultSet = false;
            foreach ($areas as $area) {
                $areaIdentifier = $area['identifier'];
                unset($area['identifier']);

                if (!$defaultSet) {
                    $area['default'] = true;
                    $defaultSet = true;
                }

                $versionGroupData['areas'][$areaIdentifier] = $area;
            }
            $destinationData[$versionGroup] = $versionGroupData;
        }

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
