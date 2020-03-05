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
 * Contest Type migration.
 *
 * @DataMigration(
 *     name="Contest Type",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/contest_type.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class ContestType extends AbstractDataMigration implements DataMigrationInterface
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
       "contest_types"."identifier",
       "contest_type_names"."name",
       "contest_type_names"."flavor" AS "berry_flavor",
       "contest_type_names"."color" AS "pokeblock_color"
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
        $sourceData['berry_flavor'] = str_replace('_', '-', Inflector::tableize($sourceData['berry_flavor']));
        $sourceData['pokeblock_color'] = str_replace('_', '-', Inflector::tableize($sourceData['pokeblock_color']));

        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
