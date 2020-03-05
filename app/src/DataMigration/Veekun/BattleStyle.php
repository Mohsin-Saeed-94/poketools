<?php

namespace App\DataMigration\Veekun;

use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * BattleStyle migration.
 *
 * @DataMigration(
 *     name="BattleStyle",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/battle_style.csv",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\CsvDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class BattleStyle extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "move_battle_styles"."id",
       "move_battle_styles"."identifier",
       "move_battle_style_prose"."name"
FROM "move_battle_styles"
     JOIN "move_battle_style_prose"
         ON "move_battle_styles"."id" = "move_battle_style_prose"."move_battle_style_id"
WHERE "move_battle_style_prose"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "move_battle_styles";
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
