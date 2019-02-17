<?php

namespace App\DataMigration;

use App\Entity\Embeddable\Range;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\Exception\MigrationException;

/**
 * Encounter migration.
 *
 * @DataMigration(
 *     name="Encounter",
 *     source="csv:///%kernel.project_dir%/resources/data/encounter.csv",
 *     sourceIds={@IdField(name="id")},
 *     destination="doctrine:///App/Entity/Encounter",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *         "App\DataMigration\Version",
 *         "App\DataMigration\Location",
 *         "App\DataMigration\EncounterMethod",
 *         "App\DataMigration\PokemonSpecies",
 *         "App\DataMigration\EncounterCondition"
 *     }
 * )
 */
class Encounter extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $intFields = [
            'id',
            'chance',
        ];
        $sourceData = $this->convertToInts($sourceData, $intFields);
        foreach ($sourceData as &$sourceDatum) {
            if ($sourceDatum === '') {
                $sourceDatum = null;
            }
        }
        $sourceData = $this->removeNulls($sourceData);

        $encounterId = $sourceData['id'];
        unset($sourceData['id']);
        static $position = 1;
        $sourceData['position'] = $position;
        $position++;

        // Version
        /** @var \App\Entity\Version $version */
        $version = $this->referenceStore->get(Version::class, ['identifier' => $sourceData['version']]);
        $versionGroup = $version->getVersionGroup();
        $sourceData['version'] = $version;

        // Location Area
        /** @var \App\Entity\Location $location */
        $location = $this->referenceStore->get(Location::class, ['identifier' => $sourceData['location']]);
        $location = $location->findChildByGrouping($versionGroup);
        $locationArea = null;
        foreach ($location->getAreas() as $checkLocationArea) {
            if ($checkLocationArea->getSlug() === $sourceData['area']) {
                $locationArea = $checkLocationArea;
                break;
            }
        }
        if (is_null($locationArea)) {
            throw new MigrationException(sprintf('Encounter %u occurs in location "%s", area "%s".  The area does not exist.', $encounterId, $location->getName(), $sourceData['area']));
        }
        $sourceData['location_area'] = $locationArea;
        unset($sourceData['location'], $sourceData['area']);

        // Method
        $sourceData['method'] = $this->referenceStore->get(EncounterMethod::class, ['identifier' => $sourceData['method']]);

        // Pokemon
        /** @var \App\Entity\PokemonSpecies $species */
        $species = $this->referenceStore->get(PokemonSpecies::class, ['identifier' => $sourceData['species']]);
        $species = $species->findChildByGrouping($versionGroup);
        $pokemon = null;
        foreach ($species->getPokemon() as $checkPokemon) {
            if ($checkPokemon->getSlug() === $sourceData['pokemon']) {
                $pokemon = $checkPokemon;
                break;
            }
        }
        if (is_null($pokemon)) {
            throw new MigrationException(sprintf('Encounter %u is with the pokemon "%s".  The pokemon does not exist.', $encounterId, $sourceData['pokemon']));
        }
        $sourceData['pokemon'] = $pokemon;
        unset($sourceData['species']);

        // Level range
        $sourceData['level'] = Range::fromString($sourceData['level']);

        // Conditions
        if (isset($sourceData['conditions'])) {
            $conditions = explode(',', $sourceData['conditions']);
            foreach ($conditions as &$condition) {
                $conditionParts = explode('/', $condition, 2);
                $condition = $conditionParts[0];
                $stateIdentifier = str_replace($condition.'-', '', $conditionParts[1]);
                /** @var \App\Entity\EncounterCondition $condition */
                $condition = $this->referenceStore->get(EncounterCondition::class, ['identifier' => $condition]);
                $state = null;
                foreach ($condition->getStates() as $checkState) {
                    if ($checkState->getSlug() == $stateIdentifier) {
                        $state = $checkState;
                        break;
                    }
                }
                if (is_null($state)) {
                    throw new MigrationException(sprintf('Encounter %u requires the state "%s".  The state does not exist.', $encounterId, $stateIdentifier));
                }
                $condition = $state;
            }
            $sourceData['conditions'] = $conditions;
        }

        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\Encounter();
    }
}
