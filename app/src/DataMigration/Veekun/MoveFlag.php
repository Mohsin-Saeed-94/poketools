<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Move Flag migration.
 *
 * @DataMigration(
 *     name="Move Flag",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/move_flag.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class MoveFlag extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "move_flags"."id",
       "move_flags"."identifier",
       "move_flag_prose"."name",
       "move_flag_prose"."description"
FROM "move_flags"
     JOIN "move_flag_prose"
         ON "move_flags"."id" = "move_flag_prose"."move_flag_id"
WHERE "move_flag_prose"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "move_flags";
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

        return $destinationData;
    }
}
