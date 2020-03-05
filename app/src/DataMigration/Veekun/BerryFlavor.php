<?php

namespace App\DataMigration\Veekun;

use Doctrine\Common\Inflector\Inflector;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Berry Flavor migration.
 *
 * @DataMigration(
 *     name="Berry Flavor",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/berry_flavor.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class BerryFlavor extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "contest_types"."id",
       "contest_type_names"."flavor" AS "name"
FROM "contest_types"
     JOIN "contest_type_names"
         ON "contest_types"."id" = "contest_type_names"."contest_type_id"
WHERE "contest_type_names"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "contest_types";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $sourceData['identifier'] = str_replace('_', '-', Inflector::tableize($sourceData['name']));
        ksort($sourceData);
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
