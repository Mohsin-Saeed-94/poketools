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
 * Move Effect migration.
 *
 * @DataMigration(
 *     name="Move Effect",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/move_effect",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class MoveEffect extends AbstractDataMigration implements DataMigrationInterface
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

        $sourceDriver->setStatement(
            <<<SQL
SELECT "move_effects"."id"
FROM "move_effects";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "move_effects";
SQL
        );

        $this->versionGroupStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier" AS "version_group",
       CASE
           WHEN min("changelog"."id") IS NULL
                 THEN "move_effect_prose"."short_effect"
           ELSE substr("changelog"."effect", 1, instr("changelog"."effect", '.'))
       END AS "short_description",
       coalesce("changelog"."effect", "move_effect_prose"."effect") AS "description"
FROM "move_effects"
     JOIN "version_groups"
     JOIN "move_effect_prose"
         ON "move_effects"."id" = "move_effect_prose"."move_effect_id"
     LEFT OUTER JOIN (SELECT "move_effect_changelog"."id",
                             "move_effect_changelog"."effect_id",
                             "move_effect_changelog"."changed_in_version_group_id",
                             "version_groups"."order" AS "version_group_order",
                             "move_effect_changelog_prose"."effect"
                      FROM "move_effect_changelog"
                           JOIN "version_groups"
                               ON "move_effect_changelog"."changed_in_version_group_id" =
                                  "version_groups"."id"
                           JOIN "move_effect_changelog_prose"
                               ON "move_effect_changelog"."id" =
                                  "move_effect_changelog_prose"."move_effect_changelog_id"
                      WHERE "move_effect_changelog_prose"."local_language_id" = 9
                      ORDER BY "version_groups"."order" ASC) "changelog"
         ON "changelog"."effect_id" = "move_effects"."id"
                AND "changelog"."version_group_order" > "version_groups"."order"
WHERE "move_effect_prose"."local_language_id" = 9 AND "move_effects"."id" = :effect_id
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
        $destinationData['id'] = $sourceData['id'];
        $versionGroupData = $this->getData($sourceData['id']);
        foreach ($versionGroupData as $versionGroupRow) {
            $versionGroup = $versionGroupRow['version_group'];
            unset($versionGroupRow['version_group']);
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
     * @param int $effectId
     *
     * @return \Doctrine\DBAL\Driver\ResultStatement
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getData(int $effectId)
    {
        $this->versionGroupStatement->execute(['effect_id' => $effectId]);

        return $this->versionGroupStatement;
    }

    /**
     * {@inheritdoc}
     * @param YamlDestinationDriver $destinationDriver
     */
    public function configureDestination(DestinationDriverInterface $destinationDriver)
    {
        $destinationDriver->setOption('refs', ['exclude' => ['`.+\.short_description`']]);
    }
}
