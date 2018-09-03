<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Growth Rate migration.
 *
 * @DataMigration(
 *     name="Growth Rate",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/growth_rate",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class GrowthRate extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "growth_rates"."id",
       "growth_rates"."identifier",
       "growth_rate_prose"."name",
       "growth_rates"."formula"
FROM "growth_rates"
     JOIN "growth_rate_prose"
         ON "growth_rates"."id" = "growth_rate_prose"."growth_rate_id"
WHERE "growth_rate_prose"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "growth_rates";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['id']);
        $sourceData['expression'] = '';
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
