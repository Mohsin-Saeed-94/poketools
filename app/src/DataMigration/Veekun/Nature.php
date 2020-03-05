<?php

namespace App\DataMigration\Veekun;

use Doctrine\DBAL\Driver\Statement;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Nature migration.
 *
 * @DataMigration(
 *     name="Nature",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/nature",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Nature extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * @var Statement
     */
    protected $battleStylePreferences;

    /**
     * @var Statement
     */
    protected $pokeathlonStatChanges;

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "natures"."id",
       "natures"."identifier",
       "nature_names"."name",
       "increased_stats"."identifier" AS "stat_increased",
       "decreased_stats"."identifier" AS "stat_decreased",
       lower("likes_berry_flavors"."flavor") AS "flavor_likes",
       lower("hates_berry_flavors"."flavor") AS "flavor_hates"
FROM "natures"
     JOIN "nature_names"
         ON "natures"."id" = "nature_names"."nature_id"
     JOIN "stats" "increased_stats"
         ON "natures"."increased_stat_id" = "increased_stats"."id"
     JOIN "stats" "decreased_stats"
         ON "natures"."decreased_stat_id" = "decreased_stats"."id"
     JOIN "contest_types" "likes_contest_types"
         ON "natures"."likes_flavor_id" = "likes_contest_types"."id"
     JOIN "contest_type_names" "likes_berry_flavors"
         ON "likes_contest_types"."id" = "likes_berry_flavors"."contest_type_id"
     JOIN "contest_types" "hates_contest_types"
         ON "natures"."hates_flavor_id" = "hates_contest_types"."id"
     JOIN "contest_type_names" "hates_berry_flavors"
         ON "hates_contest_types"."id" = "hates_berry_flavors"."contest_type_id"
WHERE "nature_names"."local_language_id" = 9
  AND "likes_berry_flavors"."local_language_id" = 9
  AND "hates_berry_flavors"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "natures";
SQL
        );

        $this->battleStylePreferences = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "move_battle_styles"."identifier" AS "battle_style",
       "nature_battle_style_preferences"."low_hp_preference" AS "low_hp_chance",
       "nature_battle_style_preferences"."high_hp_preference" AS "high_hp_chance"
FROM "nature_battle_style_preferences"
     JOIN "move_battle_styles"
         ON "nature_battle_style_preferences"."move_battle_style_id" = "move_battle_styles"."id"
WHERE "nature_battle_style_preferences"."nature_id" = :nature
ORDER BY "nature_battle_style_preferences"."move_battle_style_id";
SQL
        );

        $this->pokeathlonStatChanges = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "pokeathlon_stats"."identifier" AS "pokeathlon_stat",
       "nature_pokeathlon_stats"."max_change"
FROM "nature_pokeathlon_stats"
     JOIN "pokeathlon_stats"
         ON "nature_pokeathlon_stats"."pokeathlon_stat_id" = "pokeathlon_stats"."id"
WHERE "nature_pokeathlon_stats"."nature_id" = :nature
ORDER BY "nature_pokeathlon_stats"."pokeathlon_stat_id";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $natureId = $sourceData['id'];
        unset($sourceData['id']);
        $destinationData = array_merge($sourceData, $destinationData);

        $this->battleStylePreferences->execute(['nature' => $natureId]);
        foreach ($this->battleStylePreferences as $battleStylePreference) {
            $battleStyle = $battleStylePreference['battle_style'];
            unset($battleStylePreference['battle_style']);
            $battleStylePreference = $this->allFieldsToInt($battleStylePreference);
            $destinationData['battle_style_preferences'][$battleStyle] = array_merge($destinationData['battle_style_preferences'][$battleStyle] ?? [], $battleStylePreference);
        }

        $this->pokeathlonStatChanges->execute(['nature' => $natureId]);
        foreach ($this->pokeathlonStatChanges as $pokeathlonStatChange) {
            $pokeathlonStat = $pokeathlonStatChange['pokeathlon_stat'];
            unset($pokeathlonStatChange['pokeathlon_stat']);
            $pokeathlonStatChange = $this->allFieldsToInt($pokeathlonStatChange);
            $destinationData['pokeathlon_stat_changes'][$pokeathlonStat] = array_merge($destinationData['pokeathlon_stat_changes'][$pokeathlonStat] ?? [], $pokeathlonStatChange);
        }

        return $destinationData;
    }

    /**
     * Convert all fields in the array to int.
     *
     * @param array $data
     *
     * @return int[]
     */
    private function allFieldsToInt(array $data): array
    {
        foreach ($data as &$datum) {
            $datum = (int)$datum;
        }

        return $data;
    }
}
