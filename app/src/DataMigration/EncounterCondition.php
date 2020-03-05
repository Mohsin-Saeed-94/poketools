<?php

namespace App\DataMigration;

use App\Entity\EncounterConditionState;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Encounter Condition migration.
 *
 * @DataMigration(
 *     name="Encounter Condition",
 *     source="/%kernel.project_dir%/resources/data/encounter_condition",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\EncounterCondition",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")}
 * )
 */
class EncounterCondition extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param \App\Entity\EncounterCondition $destinationData
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);

        $statePosition = 1;
        foreach ($sourceData['states'] as $sourceStateIdentifier => $sourceState) {
            /** @var EncounterConditionState $destinationState */
            $destinationState = $destinationData->getStates()->filter(
                function (EncounterConditionState $state) use ($sourceStateIdentifier) {
                    return $state->getSlug() === $sourceStateIdentifier;
                }
            )->first() ?: (new EncounterConditionState());
            $sourceState['slug'] = $sourceStateIdentifier;
            $sourceState['position'] = $statePosition;
            $destinationState = $this->mergeProperties($sourceState, $destinationState);
            $destinationData->addState($destinationState);
            $statePosition++;
        }

        $properties = ['name', 'position'];
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\EncounterCondition();
    }
}
