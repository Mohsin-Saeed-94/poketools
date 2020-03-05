<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Contest Effect migration.
 *
 * @DataMigration(
 *     name="Contest Effect",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/contest_effect.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class ContestEffect extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "contest_effects"."id",
       "contest_effects"."appeal",
       "contest_effects"."jam",
       "contest_effect_prose"."flavor_text",
       "contest_effect_prose"."effect" AS "description"
FROM "contest_effects"
     JOIN "contest_effect_prose"
         ON "contest_effects"."id" = "contest_effect_prose"."contest_effect_id"
WHERE "contest_effect_prose"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "contest_effects";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
