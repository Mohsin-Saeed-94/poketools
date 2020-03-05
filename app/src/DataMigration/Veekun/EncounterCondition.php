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
 * Encounter Condition migration.
 *
 * @DataMigration(
 *     name="Encounter Condition",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="/%kernel.project_dir%/resources/data/encounter_condition",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class EncounterCondition extends AbstractDataMigration implements DataMigrationInterface
{

    /**
     * @var Statement
     */
    protected $stateStatement;

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "encounter_conditions"."id",
       "encounter_conditions"."identifier",
       "encounter_condition_prose"."name"
FROM "encounter_conditions"
     JOIN "encounter_condition_prose"
         ON "encounter_conditions"."id" = "encounter_condition_prose"."encounter_condition_id"
WHERE "encounter_condition_prose"."local_language_id" = 9;
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "encounter_conditions"
SQL
        );

        $this->stateStatement = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "encounter_condition_values"."identifier",
       "encounter_condition_value_prose"."name",
       "encounter_condition_values"."is_default" AS "default"
FROM "encounter_condition_values"
     JOIN "encounter_condition_value_prose"
         ON "encounter_condition_values"."id" = "encounter_condition_value_prose"."encounter_condition_value_id"
WHERE "encounter_condition_values"."encounter_condition_id" = :condition
  AND "encounter_condition_value_prose"."local_language_id" = 9;
SQL
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $conditionId = $sourceData['id'];
        unset($sourceData['id']);

        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        $this->stateStatement->execute(['condition' => $conditionId]);
        $states = $this->stateStatement->fetchAll();
        foreach ($states as $stateSourceData) {
            // Remove the condition prefix from the state identifier
            $stateIdentifier = str_replace($sourceData['identifier'].'-', '', $stateSourceData['identifier']);
            unset($stateSourceData['identifier']);
            $stateSourceData['default'] = (bool)$stateSourceData['default'];
            $sourceData['states'][$stateIdentifier] = $stateSourceData;
        }

        $destinationData['states'] = array_merge($sourceData['states'], $destinationData['states'] ?? []);
        $destinationData = array_merge($sourceData, $destinationData);

        return $destinationData;
    }
}
