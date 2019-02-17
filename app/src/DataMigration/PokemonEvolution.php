<?php

namespace App\DataMigration;

use App\Entity\EvolutionTrigger;
use App\Entity\Pokemon;
use App\Entity\PokemonEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\ConsoleInvertedEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\GenderEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\HeldItemEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\KnowsMoveEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\KnowsMoveTypeEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\LocationEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\MinimumAffectionEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\MinimumBeautyEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\MinimumHappinessEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\MinimumLevelEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\NoConditionsEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\OverworldWeatherEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\PartySpeciesEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\PartyTypeEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\PhysicalStatsDifferenceEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\TimeOfDayEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\TradedForSpeciesEvolutionCondition;
use App\Entity\PokemonEvolutionCondition\TriggerItemEvolutionCondition;
use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\VersionGroup;
use Doctrine\Common\Collections\ArrayCollection;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;

/**
 * Pokemon Evolution migration.
 *
 * @DataMigration(
 *     name="Pokemon Evolution",
 *     source="yaml:///%kernel.project_dir%/resources/data/pokemon",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="doctrine:///App/Entity/PokemonSpecies",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *         "App\DataMigration\PokemonSpecies",
 *         "App\DataMigration\Gender",
 *         "App\DataMigration\Item",
 *         "App\DataMigration\Move",
 *         "App\DataMigration\Type",
 *         "App\DataMigration\Location",
 *         "App\DataMigration\Weather",
 *         "App\DataMigration\TimeOfDay",
 *         "App\DataMigration\EvolutionTrigger"
 *     },
 *     extends="App\DataMigration\PokemonSpecies"
 * )
 */
class PokemonEvolution extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param                            $sourceData
     * @param \App\Entity\PokemonSpecies $destinationData
     *
     * @return \App\Entity\PokemonSpecies|null
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    public function transform($sourceData, $destinationData)
    {
        unset($sourceData['identifier']);
        foreach ($sourceData as $versionGroup => $versionGroupData) {
            /** @var \App\Entity\VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(\App\DataMigration\VersionGroup::class, ['identifier' => $versionGroup]);
            $speciesInVersionGroup = $destinationData->findChildByGrouping($versionGroup);
            if (!$this->transformSpecies($versionGroupData, $speciesInVersionGroup, $versionGroup)) {
                return null;
            }

        }

        return $destinationData;
    }

    /**
     * @param array                        $sourceData
     * @param PokemonSpeciesInVersionGroup $destinationData
     * @param VersionGroup                 $versionGroup
     *
     * @return PokemonSpeciesInVersionGroup|null
     */
    protected function transformSpecies(array $sourceData, PokemonSpeciesInVersionGroup $destinationData, VersionGroup $versionGroup): ?PokemonSpeciesInVersionGroup
    {
        foreach ($sourceData['pokemon'] as $pokemonIdentifier => $pokemonSourceData) {
            if (!isset($pokemonSourceData['evolution_parent'])) {
                // This Pokemon does not evolve from anything; skip
                return null;
            }

            $pokemon = null;
            foreach ($destinationData->getPokemon() as $checkPokemon) {
                if ($checkPokemon->getSlug() === $pokemonIdentifier) {
                    $pokemon = $checkPokemon;
                    break;
                }
            }
            if (!$pokemon) {
                $this->nonexistantPokemon();
            }
            $this->transformPokemon($pokemonSourceData, $pokemon, $versionGroup);
        }

        return $destinationData;
    }

    /**
     * Throw an exception when encountering a Pokemon that doesn't exist when
     * it should.
     */
    protected function nonexistantPokemon()
    {
        throw new \LogicException('The destination must already exist for Pokemon Evolution');
    }

    /**
     * @param array        $sourceData
     * @param Pokemon      $destinationData
     * @param VersionGroup $versionGroup
     *
     * @return Pokemon|null
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function transformPokemon(array $sourceData, Pokemon $destinationData, VersionGroup $versionGroup): ?Pokemon
    {
        /** @var \App\Entity\PokemonSpecies $evolutionParentSpecies */
        $evolvesFrom = explode('/', $sourceData['evolution_parent']);
        $evolutionParentSpecies = $this->referenceStore->get(PokemonSpecies::class, ['identifier' => $evolvesFrom[0]]);
        $evolutionParentSpecies = $evolutionParentSpecies->findChildByGrouping($versionGroup);
        if (!$evolutionParentSpecies) {
            throw new \DomainException(sprintf('"%s" does not exist in the "%s" version group.', $sourceData['evolution_parent'], $versionGroup->getName()));
        }
        $evolutionParentPokemon = null;
        foreach ($evolutionParentSpecies->getPokemon() as $checkPokemon) {
            if ($checkPokemon->getSlug() === $evolvesFrom[1]) {
                $evolutionParentPokemon = $checkPokemon;
                break;
            }
        }
        if (!$evolutionParentPokemon) {
            throw new \DomainException(sprintf('"%s" does not exist in the "%s" version group.', $sourceData['evolution_parent'], $versionGroup->getName()));
        }
        $sourceData['evolution_parent'] = $evolutionParentPokemon;

        $pokemonEvolutionConditions = [];
        foreach ($sourceData['evolution_conditions'] as $trigger => $conditions) {
            $trigger = $this->referenceStore->get(\App\DataMigration\EvolutionTrigger::class, ['identifier' => $trigger]);
            if (empty($conditions)) {
                $conditions['no_conditions'] = [];
            }
            foreach ($conditions as $condition => $values) {
                if (!is_array($values)) {
                    $values = [$values];
                }
                $pokemonEvolutionConditions[] = $this->createEvolutionCondition($destinationData, $versionGroup, $trigger, $condition, $values);
            }
        }
        $sourceData['evolution_conditions'] = $pokemonEvolutionConditions;

        $properties = [
            'evolution_parent',
            'evolution_conditions',
        ];
        /** @var Pokemon $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $properties);

        return $destinationData;
    }

    /**
     * @param Pokemon          $pokemon
     * @param VersionGroup     $versionGroup
     * @param EvolutionTrigger $trigger
     * @param string           $conditionName
     * @param array            $values
     *
     * @return PokemonEvolutionCondition
     */
    protected function createEvolutionCondition(Pokemon $pokemon, VersionGroup $versionGroup, EvolutionTrigger $trigger, string $conditionName, array $values): PokemonEvolutionCondition
    {
        /** @var string[] $conditionClassMap */
        // @formatter:off
        $conditionClassMap = [
            'trigger_item' => TriggerItemEvolutionCondition::class,
            'minimum_level' => MinimumLevelEvolutionCondition::class,
            'gender' => GenderEvolutionCondition::class,
            'location' => LocationEvolutionCondition::class,
            'held_item' => HeldItemEvolutionCondition::class,
            'time_of_day' => TimeOfDayEvolutionCondition::class,
            'known_move' => KnowsMoveEvolutionCondition::class,
            'known_move_type' => KnowsMoveTypeEvolutionCondition::class,
            'minimum_happiness' => MinimumHappinessEvolutionCondition::class,
            'minimum_beauty' => MinimumBeautyEvolutionCondition::class,
            'minimum_affection' => MinimumAffectionEvolutionCondition::class,
            'party_species' => PartySpeciesEvolutionCondition::class,
            'party_type' => PartyTypeEvolutionCondition::class,
            'physical_stats_difference' => PhysicalStatsDifferenceEvolutionCondition::class,
            'traded_for_species' => TradedForSpeciesEvolutionCondition::class,
            'overworld_weather' => OverworldWeatherEvolutionCondition::class,
            'console_inverted' => ConsoleInvertedEvolutionCondition::class,
            'no_conditions' => NoConditionsEvolutionCondition::class,
        ];
        // @formatter:on
        /** @var callable[] $createConditionCallableMap */
        // @formatter:off
        $createConditionCallableMap = [
            'trigger_item' => [$this, 'triggerItemEvolutionCondition'],
            'minimum_level' => [$this, 'minimumLevelEvolutionCondition'],
            'gender' => [$this, 'genderEvolutionCondition'],
            'location' => [$this, 'locationEvolutionCondition'],
            'held_item' => [$this, 'heldItemEvolutionCondition'],
            'time_of_day' => [$this, 'timeOfDayEvolutionCondition'],
            'known_move' => [$this, 'knowsMoveEvolutionCondition'],
            'known_move_type' => [$this, 'knowsMoveTypeEvolutionCondition'],
            'minimum_happiness' => [$this, 'minimumHappinessEvolutionCondition'],
            'minimum_beauty' => [$this, 'minimumBeautyEvolutionCondition'],
            'minimum_affection' => [$this, 'minimumAffectionEvolutionCondition'],
            'party_species' => [$this, 'partySpeciesEvolutionCondition'],
            'party_type' => [$this, 'partyTypeEvolutionCondition'],
            'physical_stats_difference' => [$this, 'physicalStatsDifferenceEvolutionCondition'],
            'traded_for_species' => [$this, 'tradedForSpeciesEvolutionCondition'],
            'overworld_weather' => [$this, 'overworldWeatherEvolutionCondition'],
            'console_inverted' => [$this, 'consoleInvertedEvolutionCondition'],
            'no_conditions' => [$this, 'noConditionsEvolutionCondition'],
        ];
        // @formatter:on

        // Sanity checks
        if (!isset($conditionClassMap[$conditionName])
            || !isset($createConditionCallableMap[$conditionName])
        ) {
            throw new \DomainException(sprintf('"%s" is not a valid evolution condition.', $conditionName));
        }

        // Find an existing condition
        $condition = null;
        foreach ($pokemon->getEvolutionConditions() as $checkCondition) {
            if ($checkCondition->getEvolutionTrigger() === $trigger
                && get_class($checkCondition) === $conditionClassMap[$conditionName]) {
                $condition = $checkCondition;
                break;
            }
        }
        if (!$condition) {
            /** @var PokemonEvolutionCondition $condition */
            $condition = new $conditionClassMap[$conditionName];
            $condition->setEvolutionTrigger($trigger);
        }

        $condition = call_user_func($createConditionCallableMap[$conditionName], $trigger, $condition, $versionGroup, $values);

        return $condition;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        $this->nonexistantPokemon();
    }

    /**
     * @param EvolutionTrigger                  $trigger
     * @param ConsoleInvertedEvolutionCondition $condition
     * @param VersionGroup                      $versionGroup
     * @param bool[]                            $value
     *
     * @return ConsoleInvertedEvolutionCondition
     */
    protected function consoleInvertedEvolutionCondition(EvolutionTrigger $trigger, ConsoleInvertedEvolutionCondition $condition, VersionGroup $versionGroup, array $value): ConsoleInvertedEvolutionCondition
    {
        $condition->setConsoleInverted(array_pop($value));

        return $condition;
    }

    /**
     * @param EvolutionTrigger         $trigger
     * @param GenderEvolutionCondition $condition
     * @param VersionGroup             $versionGroup
     * @param string[]                 $value
     *
     * @return GenderEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function genderEvolutionCondition(EvolutionTrigger $trigger, GenderEvolutionCondition $condition, VersionGroup $versionGroup, array $value): GenderEvolutionCondition
    {
        /** @var \App\Entity\Gender $gender */
        $gender = $this->referenceStore->get(Gender::class, ['identifier' => array_pop($value)]);
        $condition->setGender($gender);

        return $condition;
    }

    /**
     * @param EvolutionTrigger           $trigger
     * @param HeldItemEvolutionCondition $condition
     * @param VersionGroup               $versionGroup
     * @param string[]                   $value
     *
     * @return HeldItemEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function heldItemEvolutionCondition(EvolutionTrigger $trigger, HeldItemEvolutionCondition $condition, VersionGroup $versionGroup, array $value): HeldItemEvolutionCondition
    {
        $itemName = array_pop($value);
        /** @var \App\Entity\Item $item */
        $item = $this->referenceStore->get(Item::class, ['identifier' => $itemName]);
        $item = $item->findChildByGrouping($versionGroup);
        if (!$item) {
            throw new \DomainException(sprintf('The item "%s" is not available in the version group "%s".', $itemName, $versionGroup->getName()));
        }
        $condition->setHeldItem($item);

        return $condition;
    }

    /**
     * @param EvolutionTrigger            $trigger
     * @param KnowsMoveEvolutionCondition $condition
     * @param VersionGroup                $versionGroup
     * @param string[]                    $value
     *
     * @return KnowsMoveEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function knowsMoveEvolutionCondition(EvolutionTrigger $trigger, KnowsMoveEvolutionCondition $condition, VersionGroup $versionGroup, array $value): KnowsMoveEvolutionCondition
    {
        $moveName = array_pop($value);
        /** @var \App\Entity\Move $move */
        $move = $this->referenceStore->get(Move::class, ['identifier' => $moveName]);
        $move = $move->findChildByGrouping($versionGroup);
        if (!$move) {
            throw new \DomainException(sprintf('The move "%s" is not available in the version group "%s".', $moveName, $versionGroup->getName()));
        }
        $condition->setKnowsMove($move);

        return $condition;
    }

    /**
     * @param EvolutionTrigger                $trigger
     * @param KnowsMoveTypeEvolutionCondition $condition
     * @param VersionGroup                    $versionGroup
     * @param string[]                        $value
     *
     * @return KnowsMoveTypeEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function knowsMoveTypeEvolutionCondition(EvolutionTrigger $trigger, KnowsMoveTypeEvolutionCondition $condition, VersionGroup $versionGroup, array $value): KnowsMoveTypeEvolutionCondition
    {
        /** @var \App\Entity\Type $type */
        $type = $this->referenceStore->get(Type::class, ['identifier' => array_pop($value)]);
        $condition->setKnowsMoveType($type);

        return $condition;
    }

    /**
     * @param EvolutionTrigger           $trigger
     * @param LocationEvolutionCondition $condition
     * @param VersionGroup               $versionGroup
     * @param string[]                   $value
     *
     * @return LocationEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function locationEvolutionCondition(EvolutionTrigger $trigger, LocationEvolutionCondition $condition, VersionGroup $versionGroup, array $value): LocationEvolutionCondition
    {
        $locations = [];
        foreach ($value as $locationName) {
            /** @var \App\Entity\Location $location */
            $location = $this->referenceStore->get(Location::class, ['identifier' => $locationName]);
            $location = $location->findChildByGrouping($versionGroup);
            if (!$location) {
                throw new \DomainException(sprintf('The location "%s" is not available in the version group "%s".', $locationName, $versionGroup->getName()));
            }
            $locations[] = $location;
        }
        /** @var LocationEvolutionCondition $condition */
        $condition = $this->mergeProperties(['locations' => $locations], $condition);

        return $condition;
    }

    /**
     * @param EvolutionTrigger                   $trigger
     * @param MinimumAffectionEvolutionCondition $condition
     * @param VersionGroup                       $versionGroup
     * @param int[]                              $value
     *
     * @return MinimumAffectionEvolutionCondition
     */
    protected function minimumAffectionEvolutionCondition(EvolutionTrigger $trigger, MinimumAffectionEvolutionCondition $condition, VersionGroup $versionGroup, array $value): MinimumAffectionEvolutionCondition
    {
        $condition->setMinimumAffection(array_pop($value));

        return $condition;
    }

    /**
     * @param EvolutionTrigger                $trigger
     * @param MinimumBeautyEvolutionCondition $condition
     * @param VersionGroup                    $versionGroup
     * @param int[]                           $value
     *
     * @return MinimumBeautyEvolutionCondition
     */
    protected function minimumBeautyEvolutionCondition(EvolutionTrigger $trigger, MinimumBeautyEvolutionCondition $condition, VersionGroup $versionGroup, array $value): MinimumBeautyEvolutionCondition
    {
        $condition->setMinimumBeauty(array_pop($value));

        return $condition;
    }

    /**
     * @param EvolutionTrigger                   $trigger
     * @param MinimumHappinessEvolutionCondition $condition
     * @param VersionGroup                       $versionGroup
     * @param int[]                              $value
     *
     * @return MinimumHappinessEvolutionCondition
     */
    protected function minimumHappinessEvolutionCondition(EvolutionTrigger $trigger, MinimumHappinessEvolutionCondition $condition, VersionGroup $versionGroup, array $value): MinimumHappinessEvolutionCondition
    {
        $condition->setMinimumHappiness(array_pop($value));

        return $condition;
    }

    /**
     * @param EvolutionTrigger               $trigger
     * @param MinimumLevelEvolutionCondition $condition
     * @param VersionGroup                   $versionGroup
     * @param int[]                          $value
     *
     * @return MinimumLevelEvolutionCondition
     */
    protected function minimumLevelEvolutionCondition(EvolutionTrigger $trigger, MinimumLevelEvolutionCondition $condition, VersionGroup $versionGroup, array $value): MinimumLevelEvolutionCondition
    {
        $condition->setMinimumLevel(array_pop($value));

        return $condition;
    }

    /**
     * @param EvolutionTrigger               $trigger
     * @param NoConditionsEvolutionCondition $condition
     * @param VersionGroup                   $versionGroup
     * @param array                          $value
     *
     * @return NoConditionsEvolutionCondition
     */
    protected function noConditionsEvolutionCondition(EvolutionTrigger $trigger, NoConditionsEvolutionCondition $condition, VersionGroup $versionGroup, array $value): NoConditionsEvolutionCondition
    {
        return $condition;
    }

    /**
     * @param EvolutionTrigger                   $trigger
     * @param OverworldWeatherEvolutionCondition $condition
     * @param VersionGroup                       $versionGroup
     * @param string[]                           $value
     *
     * @return OverworldWeatherEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function overworldWeatherEvolutionCondition(EvolutionTrigger $trigger, OverworldWeatherEvolutionCondition $condition, VersionGroup $versionGroup, array $value): OverworldWeatherEvolutionCondition
    {
        /** @var \App\Entity\Weather $weather */
        $weather = $this->referenceStore->get(Weather::class, ['identifier' => array_pop($value)]);
        $condition->setOverworldWeather($weather);

        return $condition;
    }

    /**
     * @param EvolutionTrigger               $trigger
     * @param PartySpeciesEvolutionCondition $condition
     * @param VersionGroup                   $versionGroup
     * @param string[]                       $value
     *
     * @return PartySpeciesEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function partySpeciesEvolutionCondition(EvolutionTrigger $trigger, PartySpeciesEvolutionCondition $condition, VersionGroup $versionGroup, array $value): PartySpeciesEvolutionCondition
    {
        $speciesName = array_pop($value);
        /** @var \App\Entity\PokemonSpecies $species */
        $species = $this->referenceStore->get(PokemonSpecies::class, ['identifier' => $speciesName]);
        $species = $species->findChildByGrouping($versionGroup);
        if (!$species) {
            throw new \DomainException(sprintf('The species "%s" is not available in the version group "%s".', $speciesName, $versionGroup->getName()));
        }
        $condition->setPartySpecies($species);

        return $condition;
    }

    /**
     * @param EvolutionTrigger            $trigger
     * @param PartyTypeEvolutionCondition $condition
     * @param VersionGroup                $versionGroup
     * @param string[]                    $value
     *
     * @return PartyTypeEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function partyTypeEvolutionCondition(EvolutionTrigger $trigger, PartyTypeEvolutionCondition $condition, VersionGroup $versionGroup, array $value): PartyTypeEvolutionCondition
    {
        /** @var \App\Entity\Type $type */
        $type = $this->referenceStore->get(Type::class, ['identifier' => array_pop($value)]);
        $condition->setPartyType($type);

        return $condition;
    }

    /**
     * @param EvolutionTrigger                          $trigger
     * @param PhysicalStatsDifferenceEvolutionCondition $condition
     * @param VersionGroup                              $versionGroup
     * @param int[]                                     $value
     *
     * @return PhysicalStatsDifferenceEvolutionCondition
     */
    protected function physicalStatsDifferenceEvolutionCondition(EvolutionTrigger $trigger, PhysicalStatsDifferenceEvolutionCondition $condition, VersionGroup $versionGroup, array $value): PhysicalStatsDifferenceEvolutionCondition
    {
        $condition->setPhysicalStatsDifference(array_pop($value));

        return $condition;
    }

    /**
     * @param EvolutionTrigger            $trigger
     * @param TimeOfDayEvolutionCondition $condition
     * @param VersionGroup                $versionGroup
     * @param string[]                    $value
     *
     * @return TimeOfDayEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function timeOfDayEvolutionCondition(EvolutionTrigger $trigger, TimeOfDayEvolutionCondition $condition, VersionGroup $versionGroup, array $value): TimeOfDayEvolutionCondition
    {
        /** @var \App\Entity\TimeOfDay $time */
        $time = $this->referenceStore->get(
            TimeOfDay::class, [
                'generation' => $versionGroup->getGeneration()->getNumber(),
                'identifier' => array_pop($value),
            ]
        );
        $condition->setTimeOfDay($time);

        return $condition;
    }

    /**
     * @param EvolutionTrigger                   $trigger
     * @param TradedForSpeciesEvolutionCondition $condition
     * @param VersionGroup                       $versionGroup
     * @param string[]                           $value
     *
     * @return TradedForSpeciesEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function tradedForSpeciesEvolutionCondition(EvolutionTrigger $trigger, TradedForSpeciesEvolutionCondition $condition, VersionGroup $versionGroup, array $value): TradedForSpeciesEvolutionCondition
    {
        $speciesName = array_pop($value);
        /** @var \App\Entity\PokemonSpecies $species */
        $species = $this->referenceStore->get(PokemonSpecies::class, ['identifier' => $speciesName]);
        $species = $species->findChildByGrouping($versionGroup);
        if (!$species) {
            throw new \DomainException(sprintf('The species "%s" is not available in the version group "%s".', $speciesName, $versionGroup->getName()));
        }
        $condition->setTradedForSpecies($species);

        return $condition;
    }

    /**
     * @param EvolutionTrigger              $trigger
     * @param TriggerItemEvolutionCondition $condition
     * @param VersionGroup                  $versionGroup
     * @param string[]                      $value
     *
     * @return TriggerItemEvolutionCondition
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     */
    protected function triggerItemEvolutionCondition(EvolutionTrigger $trigger, TriggerItemEvolutionCondition $condition, VersionGroup $versionGroup, array $value): TriggerItemEvolutionCondition
    {
        $itemName = array_pop($value);
        /** @var \App\Entity\Item $item */
        $item = $this->referenceStore->get(Item::class, ['identifier' => $itemName]);
        $item = $item->findChildByGrouping($versionGroup);
        if (!$item) {
            throw new \DomainException(sprintf('The item "%s" is not available in the version group "%s".', $itemName, $versionGroup->getName()));
        }
        $condition->setTriggerItem($item);

        return $condition;
    }
}
