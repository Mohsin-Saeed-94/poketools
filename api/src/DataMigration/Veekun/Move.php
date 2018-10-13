<?php

namespace App\DataMigration\Veekun;

use Doctrine\DBAL\Driver\Statement;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Move migration.
 *
 * @DataMigration(
 *     name="Move",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/move",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Move extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * @var Statement
     */
    protected $versionGroupData;

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "moves"."id",
       "moves"."identifier",
       "move_names"."name",
       "move_meta_categories"."identifier" AS "category",
       CASE
           WHEN "move_meta"."meta_ailment_id" <> 0
               THEN "move_meta_ailments"."identifier"
       END AS "ailment",
       coalesce("move_meta"."min_hits", 1) AS "hits_min",
       coalesce("move_meta"."max_hits", 1) AS "hits_max",
       coalesce("move_meta"."min_turns", 1) AS "turns_min",
       coalesce("move_meta"."max_turns", 1) AS "turns_max",
       CASE
           WHEN "move_meta"."drain" > 0
               THEN "move_meta"."drain"
       END AS "drain",
       CASE
           WHEN "move_meta"."drain" < 0
               THEN abs("move_meta"."drain")
       END AS "recoil",
       nullif("move_meta"."healing", 0) AS "healing",
       nullif("move_meta"."crit_rate", 0) AS "crit_rate_bonus",
       nullif("move_meta"."ailment_chance", 0) AS "ailment_chance",
       nullif("move_meta"."flinch_chance", 0) AS "flinch_chance",
       nullif("move_meta"."stat_chance", 0) AS "stat_change_chance",
       group_concat("move_flags"."identifier", ',') AS "flags"
FROM "moves"
     JOIN "move_names"
          ON "moves"."id" = "move_names"."move_id"
     LEFT OUTER JOIN "move_meta"
                     ON "moves"."id" = "move_meta"."move_id"
     LEFT OUTER JOIN "move_meta_categories"
                     ON coalesce("move_meta"."meta_category_id", 0) = "move_meta_categories"."id"
     LEFT OUTER JOIN "move_meta_ailments"
                     ON "move_meta"."meta_ailment_id" = "move_meta_ailments"."id"
     LEFT OUTER JOIN "move_flag_map"
                     ON "moves"."id" = "move_flag_map"."move_id"
     LEFT OUTER JOIN "move_flags"
                     ON "move_flag_map"."move_flag_id" = "move_flags"."id"
WHERE "move_names"."local_language_id" = 9
GROUP BY "moves"."id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "moves";
SQL
        );

        $this->versionGroupData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier" AS "version_group",
       coalesce("changelog"."type", "types"."identifier") AS "type",
       coalesce("changelog"."power", "moves"."power") AS "power",
       coalesce("changelog"."pp", "moves"."pp") AS "pp",
       coalesce("changelog"."accuracy", "moves"."accuracy") AS "accuracy",
       coalesce("changelog"."priority", "moves"."priority") AS "priority",
       coalesce("changelog"."target", "move_targets"."identifier") AS "target",
       CASE
           WHEN "version_groups"."generation_id" < 4 AND coalesce("changelog"."type", "types"."identifier") <> 'shadow'
               THEN "type_damage_classes"."identifier"
           ELSE "move_damage_classes"."identifier"
       END AS "damage_class",
       coalesce("changelog"."effect", "moves"."effect_id") AS "effect",
       coalesce("changelog"."effect_chance", "moves"."effect_chance") AS "effect_chance",
       CASE
           WHEN "version_groups"."identifier" IN
               ('ruby-sapphire', 'emerald', 'diamond-pearl', 'platinum', 'omega-ruby-alpha-sapphire')
               THEN "contest_types"."identifier"
       END AS "contest_type",
       CASE
           WHEN "version_groups"."identifier" IN('ruby-sapphire', 'emerald', 'omega-ruby-alpha-sapphire')
               THEN "moves"."contest_effect_id"
       END AS "contest_effect",
       CASE
           WHEN "version_groups"."identifier" IN('ruby-sapphire', 'emerald', 'omega-ruby-alpha-sapphire')
               THEN group_concat(DISTINCT "contest_combo_use_before"."identifier")
           ELSE NULL
       END AS 'contest_use_before',
       CASE
           WHEN "version_groups"."identifier" IN('diamond-pearl', 'platinum')
               THEN "moves"."super_contest_effect_id"
       END AS "super_contest_effect",
       CASE
           WHEN "version_groups"."identifier" IN('diamond-pearl', 'platinum')
               THEN group_concat(DISTINCT "super_contest_combo_use_before"."identifier")
           ELSE NULL
       END AS "super_contest_use_before",
       "move_flavor_text"."flavor_text"
FROM "moves"
     JOIN "version_groups"
     LEFT OUTER JOIN (
                         SELECT "move_changelog"."move_id",
                                "move_changelog"."changed_in_version_group_id",
                                "version_groups"."order" AS "version_group_order",
                                "types"."identifier" AS "type",
                                "types"."damage_class_id" AS "type_damage_class_id",
                                "move_changelog"."power",
                                "move_changelog"."pp",
                                "move_changelog"."accuracy",
                                "move_changelog"."priority",
                                "move_targets"."identifier" AS "target",
                                "move_changelog"."effect_id" AS "effect",
                                "move_changelog"."effect_chance"
                         FROM "move_changelog"
                              JOIN "version_groups"
                                   ON "move_changelog"."changed_in_version_group_id" =
                                       "version_groups"."id"
                              LEFT OUTER JOIN "types"
                                              ON "move_changelog"."type_id" = "types"."id"
                              LEFT OUTER JOIN "move_targets"
                                              ON "move_changelog"."target_id" = "move_targets"."id"
                         WHERE "move_changelog"."move_id" = :move
                         ORDER BY "version_groups"."order" ASC
                     ) "changelog"
                     ON "changelog"."version_group_order" > "version_groups"."order"
     LEFT OUTER JOIN "move_flavor_text"
                     ON "moves"."id" = "move_flavor_text"."move_id" AND
                         "version_groups"."id" = "move_flavor_text"."version_group_id"
     LEFT OUTER JOIN "types"
                     ON "moves"."type_id" = "types"."id"
     LEFT OUTER JOIN "move_targets"
                     ON "moves"."target_id" = "move_targets"."id"
     LEFT OUTER JOIN "move_damage_classes" "type_damage_classes"
                     ON coalesce("changelog"."type_damage_class_id", "types"."damage_class_id") = "type_damage_classes"."id"
     LEFT OUTER JOIN "move_damage_classes"
                     ON "moves"."damage_class_id" = "move_damage_classes"."id"
     LEFT OUTER JOIN "contest_types"
                     ON "moves"."contest_type_id" = "contest_types"."id"
     LEFT OUTER JOIN "contest_combos"
                     ON "moves"."id" = "contest_combos"."first_move_id"
     LEFT OUTER JOIN "moves" "contest_combo_use_before"
                     ON "contest_combos"."second_move_id" = "contest_combo_use_before"."id"
     LEFT OUTER JOIN "super_contest_combos"
                     ON "moves"."id" = "super_contest_combos"."first_move_id"
     LEFT OUTER JOIN "moves" "super_contest_combo_use_before"
                     ON "super_contest_combos"."second_move_id" = "super_contest_combo_use_before"."id"
WHERE ("move_flavor_text"."language_id" = 9 OR "move_flavor_text"."language_id" IS NULL)
  AND "moves"."id" = :move
  AND CASE
          WHEN "moves"."identifier" = 'shadow-rush'
              THEN "version_groups"."identifier" = 'colosseum'
          WHEN "types"."identifier" = 'shadow'
              THEN "version_groups"."identifier" IN('colosseum', 'xd')
          ELSE "version_groups"."generation_id" >= "moves"."generation_id"
      END
GROUP BY "version_groups"."id"
ORDER BY "version_groups"."order";
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $moveId = $sourceData['id'];
        $destinationData['identifier'] = $sourceData['identifier'];
        unset($sourceData['id'], $sourceData['identifier']);
        $sourceData['categories'] = explode('+', $sourceData['category']);
        unset($sourceData['category']);
        $sourceData['hits'] = $this->buildRangeString($sourceData['hits_min'], $sourceData['hits_max']);
        unset($sourceData['hits_min'], $sourceData['hits_max']);
        $sourceData['turns'] = $this->buildRangeString($sourceData['turns_min'], $sourceData['turns_max']);
        unset($sourceData['turns_min'], $sourceData['turns_max']);
        if (isset($sourceData['flags'])) {
            $sourceData['flags'] = explode(',', $sourceData['flags']);
        }

        $sourceData = $this->removeNulls($sourceData);
        $intFields = [
            'drain',
            'recoil',
            'healing',
            'crit_rate_bonus',
            'ailment_chance',
            'flinch_chance',
            'stat_change_chance',
        ];
        $sourceData = $this->convertToInts($sourceData, $intFields);

        $this->versionGroupData->execute(['move' => $moveId]);
        foreach ($this->versionGroupData as $versionGroupSourceData) {
            $versionGroup = $versionGroupSourceData['version_group'];
            unset($versionGroupSourceData['version_group']);

            if (isset($versionGroupSourceData['contest_use_before'])) {
                $versionGroupSourceData['contest_use_before'] = explode(',', $versionGroupSourceData['contest_use_before']);
            }
            if (isset($versionGroupSourceData['super_contest_use_before'])) {
                $versionGroupSourceData['super_contest_use_before'] = explode(',', $versionGroupSourceData['super_contest_use_before']);
            }

            $versionGroupSourceData = $this->removeNulls($versionGroupSourceData);
            $intFields = [
                'power',
                'pp',
                'accuracy',
                'priority',
                'effect',
                'effect_chance',
                'contest_effect',
                'super_contest_effect',
            ];
            $versionGroupSourceData = $this->convertToInts($versionGroupSourceData, $intFields);
            $destinationData[$versionGroup] = array_merge($sourceData, $versionGroupSourceData, $destinationData[$versionGroup] ?? []);
        }

        return $destinationData;
    }

    private function buildRangeString(int $min, int $max)
    {
        if ($min === $max) {
            return $min;
        } else {
            return "$min-$max";
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function removeNulls(array $data)
    {
        return array_filter(
            $data,
            function ($value) {
                return !is_null($value);
            }
        );
    }

    /**
     * @param array $data
     * @param array $fields
     *
     * @return array
     */
    private function convertToInts(array $data, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = (int)$data[$field];
            }
        }

        return $data;
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
