<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Move Ailment migration.
 *
 * @DataMigration(
 *     name="Move Ailment",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/move_ailment.csv",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class MoveAilment extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "move_meta_ailments"."id",
       "move_meta_ailments"."identifier",
       "move_meta_ailment_names"."name",
       0 AS "volatile"
FROM "move_meta_ailments"
     JOIN "move_meta_ailment_names"
         ON "move_meta_ailments"."id" = "move_meta_ailment_names"."move_meta_ailment_id"
WHERE "move_meta_ailment_names"."local_language_id" = 9;

SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "move_meta_ailments";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $sourceData['volatile'] = (int)$sourceData['volatile'];
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
