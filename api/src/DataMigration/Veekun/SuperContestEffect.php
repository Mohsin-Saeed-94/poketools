<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Super Contest Effect migration.
 *
 * @DataMigration(
 *     name="Super Contest Effect",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="csv:///%kernel.project_dir%/resources/data/super_contest_effect.csv",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class SuperContestEffect extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "super_contest_effects"."id",
       "super_contest_effects"."appeal",
       "prose"."flavor_text"
FROM "super_contest_effects"
     JOIN "super_contest_effect_prose" "prose" ON "super_contest_effects"."id" = "prose"."super_contest_effect_id"
WHERE "prose"."local_language_id" = 9;
SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "super_contest_effects"
SQL
        );
        $sourceDriver->setCountStatement($countStatement);
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
