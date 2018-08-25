<?php

namespace App\DataMigration\Veekun;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\AbstractDataMigration;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;

/**
 * Ability migration.
 *
 * @DataMigration(
 *     name="Ability",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/ability",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class Ability extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Statement
     */
    protected $versionGroupStatement;

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $this->connection = $sourceDriver->getConnection();

        $statement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "abilities"."id",
       "abilities"."identifier",
       "ability_names"."name"
FROM "abilities"
     JOIN "ability_names"
         ON "abilities"."id" = "ability_names"."ability_id"
WHERE "ability_names"."local_language_id" = 9
  AND "is_main_series" = 1;

SQL
        );
        $sourceDriver->setStatement($statement);

        $countStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT count(*)
FROM "abilities"
WHERE "is_main_series" = 1;
SQL
        );
        $sourceDriver->setCountStatement($countStatement);

        $this->versionGroupStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier" AS "version_group",
       CASE
           WHEN min("changelog"."id") IS NULL
                 THEN "ability_prose"."short_effect"
           ELSE substr("changelog"."effect", 1, instr("changelog"."effect", '.'))
       END AS "short_effect",
       coalesce("changelog"."effect", "ability_prose"."effect") AS "effect",
       "ability_flavor_text"."flavor_text"
FROM "abilities"
     JOIN "version_groups"
     JOIN "ability_prose"
         ON "abilities"."id" = "ability_prose"."ability_id"
     LEFT OUTER JOIN (SELECT "ability_changelog"."id",
                             "ability_changelog"."ability_id",
                             "ability_changelog"."changed_in_version_group_id",
                             "version_groups"."order" AS "version_group_order",
                             "ability_changelog_prose"."effect"
                      FROM "ability_changelog"
                           JOIN "version_groups"
                               ON "ability_changelog"."changed_in_version_group_id" =
                                  "version_groups"."id"
                           JOIN "ability_changelog_prose"
                               ON "ability_changelog"."id" =
                                  "ability_changelog_prose"."ability_changelog_id"
                      WHERE "ability_changelog_prose"."local_language_id" = 9
                      ORDER BY "version_groups"."order" ASC) "changelog"
         ON "changelog"."ability_id" = "abilities"."id"
                AND "changelog"."version_group_order" > "version_groups"."order"
     LEFT OUTER JOIN "ability_flavor_text"
         ON "abilities"."id" = "ability_flavor_text"."ability_id" AND
            "version_groups"."id" = "ability_flavor_text"."version_group_id"
WHERE "ability_prose"."local_language_id" = 9
  AND ("ability_flavor_text"."language_id" = 9 OR "ability_flavor_text"."language_id" IS NULL)
  AND "version_groups"."generation_id" >= "abilities"."generation_id"
  AND "abilities"."id" = :ability_id
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
        $destinationData['identifier'] = $sourceData['identifier'];
        $versionGroupData = $this->getData($sourceData['id']);
        foreach ($versionGroupData as $versionGroupRow) {
            $versionGroup = $versionGroupRow['version_group'];
            unset($versionGroupRow['version_group']);

            // Use Ruby/Sapphire flavor text in Colosseum/XD
            $isColosseumXd = in_array($versionGroup, ['colosseum', 'xd']);
            if (!isset($versionGroupRow['flavor_text']) && $isColosseumXd) {
                $versionGroupRow['flavor_text'] = $destinationData['ruby-sapphire']['flavor_text'];
            }

            $destinationData[$versionGroup] = array_merge(
                $versionGroupRow,
                $destinationData[$versionGroup] ?? []
            );
        }

        return $destinationData;
    }

    /**
     * Get version group specific data
     *
     * @param int $abilityId
     *
     * @return \Doctrine\DBAL\Driver\ResultStatement
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getData(int $abilityId)
    {
        $this->versionGroupStatement->execute(['ability_id' => $abilityId]);

        return $this->versionGroupStatement;
    }

    /**
     * {@inheritdoc}
     * @param YamlDestinationDriver $destinationDriver
     */
    public function configureDestination(DestinationDriverInterface $destinationDriver)
    {
        $destinationDriver->setOption('refs', ['exclude' => ['`.+\.short_effect`']]);
    }
}
