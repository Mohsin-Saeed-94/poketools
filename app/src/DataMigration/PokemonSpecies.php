<?php

namespace App\DataMigration;

use App\Entity\Embeddable\Range;
use App\Entity\Media\PokemonArt;
use App\Entity\Media\PokemonSprite;
use App\Entity\Pokemon;
use App\Entity\PokemonAbility;
use App\Entity\PokemonFlavorText;
use App\Entity\PokemonForm;
use App\Entity\PokemonFormPokeathlonStat;
use App\Entity\PokemonPalParkData;
use App\Entity\PokemonSpeciesInVersionGroup;
use App\Entity\PokemonSpeciesPokedexNumber;
use App\Entity\PokemonStat;
use App\Entity\PokemonType;
use App\Entity\PokemonWildHeldItem;
use App\Entity\VersionGroup;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

/**
 * Pokemon Species, Pokemon, and Pokemon Form migration.
 *
 * @DataMigration(
 *     name="Pokemon",
 *     source="/%kernel.project_dir%/resources/data/pokemon",
 *     sourceDriver="DragoonBoots\A2B\Drivers\Source\YamlSourceDriver",
 *     sourceIds={@IdField(name="identifier", type="string")},
 *     destination="\App\Entity\PokemonSpecies",
 *     destinationDriver="DragoonBoots\A2B\Drivers\Destination\DoctrineDestinationDriver",
 *     destinationIds={@IdField(name="id")},
 *     depends={
 *         "App\DataMigration\VersionGroup",
 *         "App\DataMigration\Version",
 *         "App\DataMigration\PokemonColor",
 *         "App\DataMigration\PokemonShape",
 *         "App\DataMigration\PokemonHabitat",
 *         "App\DataMigration\GrowthRate",
 *         "App\DataMigration\Pokedex",
 *         "App\DataMigration\Type",
 *         "App\DataMigration\EggGroup",
 *         "App\DataMigration\Ability",
 *         "App\DataMigration\Item",
 *         "App\DataMigration\Stat",
 *         "App\DataMigration\PokeathlonStat",
 *         "App\DataMigration\PalParkArea"
 *     }
 * )
 */
class PokemonSpecies extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * {@inheritdoc}
     * @param                            $sourceData
     * @param \App\Entity\PokemonSpecies $destinationData
     *
     * @return \App\Entity\PokemonSpecies
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     * @throws \PhpUnitsOfMeasure\Exception\NonNumericValue
     * @throws \PhpUnitsOfMeasure\Exception\NonStringUnitName
     */
    public function transform($sourceData, $destinationData)
    {
        $speciesIdentifier = $sourceData['identifier'];
        unset($sourceData['identifier']);
        foreach ($sourceData as $versionGroup => $versionGroupSourceData) {
            /** @var VersionGroup $versionGroup */
            $versionGroup = $this->referenceStore->get(
                \App\DataMigration\VersionGroup::class,
                ['identifier' => $versionGroup]
            );
            $speciesInVersionGroup = $destinationData->findChildByGrouping(
                    $versionGroup
                ) ?? new PokemonSpeciesInVersionGroup();
            $speciesInVersionGroup->setVersionGroup($versionGroup)
                ->setSlug($speciesIdentifier);
            $speciesInVersionGroup = $this->transformSpecies($versionGroupSourceData, $speciesInVersionGroup);
            $destinationData->addChild($speciesInVersionGroup);
        }

        return $destinationData;
    }

    /**
     * @param array $sourceData
     * @param PokemonSpeciesInVersionGroup $destinationData
     *
     * @return PokemonSpeciesInVersionGroup
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     * @throws \PhpUnitsOfMeasure\Exception\NonNumericValue
     * @throws \PhpUnitsOfMeasure\Exception\NonStringUnitName
     */
    protected function transformSpecies(
        array $sourceData,
        PokemonSpeciesInVersionGroup $destinationData
    ): PokemonSpeciesInVersionGroup {
        $versionGroup = $destinationData->getVersionGroup();

        // Pokedex numbers
        if (isset($sourceData['numbers'])) {
            foreach ($sourceData['numbers'] as $pokedex => $number) {
                $nationalPokedex = ($pokedex === 'national');
                /** @var \App\Entity\Pokedex $pokedex */
                $pokedex = $this->referenceStore->get(Pokedex::class, ['identifier' => $pokedex]);
                $pokedexNumber = null;
                foreach ($destinationData->getNumbers() as $checkPokedexNumber) {
                    if ($checkPokedexNumber->getPokedex() === $pokedex) {
                        $pokedexNumber = $checkPokedexNumber;
                        break;
                    }
                }
                if (!$pokedexNumber) {
                    $pokedexNumber = new PokemonSpeciesPokedexNumber();
                    $pokedexNumber->setPokedex($pokedex);
                }
                $pokedexNumber->setNumber($number);
                if ($nationalPokedex) {
                    $pokedexNumber->setDefault(true);
                } else {
                    $pokedexNumber->setDefault(false);
                }
                $destinationData->addNumber($pokedexNumber);
            }
        }

        // Pokemon
        $pokemonPosition = 1;
        foreach ($sourceData['pokemon'] as $pokemonIdentifier => $pokemonSourceData) {
            $pokemon = null;
            foreach ($destinationData->getPokemon() as $checkPokemon) {
                if ($checkPokemon->getSlug() === $pokemonIdentifier) {
                    $pokemon = $checkPokemon;
                    break;
                }
            }
            if (!$pokemon) {
                $pokemon = new Pokemon();
            }
            $pokemon->setSlug($pokemonIdentifier)
                ->setPosition($pokemonPosition);

            $pokemon = $this->transformPokemon($pokemonSourceData, $pokemon, $versionGroup);
            $destinationData->addPokemon($pokemon);
            $pokemonPosition++;
        }

        $fields = [
            'name',
            'position',
        ];
        /** @var PokemonSpeciesInVersionGroup $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $fields);

        return $destinationData;
    }

    /**
     * @param array $sourceData
     * @param Pokemon $destinationData
     * @param VersionGroup $versionGroup
     *
     * @return Pokemon
     * @throws \DragoonBoots\A2B\Exception\NoMappingForIdsException
     * @throws \DragoonBoots\A2B\Exception\NonexistentDriverException
     * @throws \DragoonBoots\A2B\Exception\NonexistentMigrationException
     * @throws \PhpUnitsOfMeasure\Exception\NonNumericValue
     * @throws \PhpUnitsOfMeasure\Exception\NonStringUnitName
     */
    protected function transformPokemon(
        array $sourceData,
        Pokemon $destinationData,
        VersionGroup $versionGroup
    ): Pokemon {
        // Pokemon habitat
        if (isset($sourceData['habitat'])) {
            $sourceData['habitat'] = $this->referenceStore->get(
                PokemonHabitat::class,
                ['identifier' => $sourceData['habitat']]
            );
        }

        // Pokemon color
        if (isset($sourceData['color'])) {
            $sourceData['color'] = $this->referenceStore->get(
                PokemonColor::class,
                ['identifier' => $sourceData['color']]
            );
        }

        // Pokemon shape
        if (isset($sourceData['shape'])) {
            /** @var \App\Entity\PokemonShape $shape */
            $shape = $this->referenceStore->get(PokemonShape::class, ['identifier' => $sourceData['shape']]);
            $sourceData['shape'] = $shape->findChildByGrouping($versionGroup);
        }

        // Growth rate
        $sourceData['growth_rate'] = $this->referenceStore->get(
            GrowthRate::class,
            ['identifier' => $sourceData['growth_rate']]
        );

        // Height and Weight
        $sourceData['height'] = new Length($sourceData['height'], 'dm');
        $sourceData['weight'] = new Mass($sourceData['weight'], 'hg');

        // Types
        $types = [];
        $typePosition = 1;
        foreach ($sourceData['types'] as $type) {
            /** @var \App\Entity\Type $type */
            $type = $this->referenceStore->get(Type::class, ['identifier' => $type]);
            $pokemonType = null;
            foreach ($destinationData->getTypes() as $checkPokemonType) {
                if ($checkPokemonType->getType() === $type) {
                    $pokemonType = $checkPokemonType;
                }
            }
            if (!$pokemonType) {
                $pokemonType = new PokemonType();
                $pokemonType->setType($type);
            }
            $pokemonType->setPosition($typePosition);
            $types[] = $pokemonType;
            $typePosition++;
        }
        $sourceData['types'] = $types;

        // Egg groups
        if (isset($sourceData['egg_groups'])) {
            foreach ($sourceData['egg_groups'] as &$eggGroup) {
                $eggGroup = $this->referenceStore->get(EggGroup::class, ['identifier' => $eggGroup]);
            }
        }

        // Stats
        $stats = [];
        foreach ($sourceData['stats'] as $stat => $statData) {
            /** @var \App\Entity\Stat $stat */
            $stat = $this->referenceStore->get(Stat::class, ['identifier' => $stat]);
            $pokemonStat = $destinationData->getStatData($stat) ?? new PokemonStat();
            $pokemonStat->setStat($stat);
            $pokemonStat->setPosition($stat->getPosition())
                ->setBaseValue($statData['base_value'])
                ->setEffortChange($statData['effort_change']);
            $stats[] = ($pokemonStat);
        }
        $sourceData['stats'] = $stats;

        // Abilities
        if (isset($sourceData['abilities'])) {
            $abilities = [];
            foreach ($sourceData['abilities'] as $ability => $abilityData) {
                /** @var \App\Entity\Ability $ability */
                $ability = $this->referenceStore->get(Ability::class, ['identifier' => $ability]);
                $ability = $ability->findChildByGrouping($versionGroup);
                $pokemonAbility = $destinationData->getAbilityData($ability) ?? new PokemonAbility();
                $pokemonAbility->setAbility($ability);
                $pokemonAbility->setPosition($abilityData['position'])
                    ->setHidden($abilityData['hidden']);
                $abilities[] = $pokemonAbility;
            }
            $sourceData['abilities'] = $abilities;
        }

        // Wild Held Items
        if (isset($sourceData['wild_held_items'])) {
            $wildHeldItems = [];
            foreach ($sourceData['wild_held_items'] as $version => $heldItems) {
                /** @var \App\Entity\Version $version */
                $version = $this->referenceStore->get(Version::class, ['identifier' => $version]);
                foreach ($heldItems as $item => $rate) {
                    /** @var \App\Entity\Item $item */
                    $item = $this->referenceStore->get(Item::class, ['identifier' => $item]);
                    $item = $item->findChildByGrouping($versionGroup);
                    $wildHeldItem = null;
                    foreach ($destinationData->getWildHeldItemsInVersion($version) as $checkWildHeldItem) {
                        if ($checkWildHeldItem->getItem() === $item) {
                            $wildHeldItem = $checkWildHeldItem;
                            break;
                        }
                    }
                    if (!$wildHeldItem) {
                        $wildHeldItem = new PokemonWildHeldItem();
                        $wildHeldItem->setVersion($version)
                            ->setItem($item);
                    }
                    $wildHeldItem->setRate($rate);
                    $wildHeldItems[] = $wildHeldItem;
                }
            }
            $sourceData['wild_held_items'] = $wildHeldItems;
        }

        // Flavor text
        if (isset($sourceData['flavor_text'])) {
            $flavorTexts = [];
            foreach ($sourceData['flavor_text'] as $version => $flavorText) {
                $version = $this->referenceStore->get(Version::class, ['identifier' => $version]);
                $pokemonFlavorText = $destinationData->getFlavorTextInVersion($version) ?? new PokemonFlavorText();
                $pokemonFlavorText->setVersion($version)
                    ->setFlavorText($flavorText);
                $flavorTexts[] = $pokemonFlavorText;
            }
            $sourceData['flavor_text'] = $flavorTexts;
        }

        // Pal park
        if (isset($sourceData['pal_park'])) {
            $palPark = $destinationData->getPalParkData();
            if ($palPark === null) {
                $palPark = new PokemonPalParkData();
            }
            $palParkArea = $this->referenceStore->get(
                PalParkArea::class,
                ['identifier' => $sourceData['pal_park']['area']]
            );
            $palPark->setArea($palParkArea);
            $palPark->setRate($sourceData['pal_park']['rate']);
            $palPark->setScore($sourceData['pal_park']['score']);
            $destinationData->setPalParkData($palPark);
        } else {
            $destinationData->setPalParkData(null);
        }

        // Forms
        $formPosition = 1;
        foreach ($sourceData['forms'] as $formIdentifier => $formSourceData) {
            $pokemonForm = null;
            foreach ($destinationData->getForms() as $checkPokemonForm) {
                if ($checkPokemonForm->getSlug() === $formIdentifier) {
                    $pokemonForm = $checkPokemonForm;
                    break;
                }
            }
            if (!$pokemonForm) {
                $pokemonForm = new PokemonForm();
            }
            $pokemonForm->setSlug($formIdentifier)
                ->setPosition($formPosition);

            $pokemonForm = $this->transformForm($formSourceData, $pokemonForm, $versionGroup);
            $destinationData->addForm($pokemonForm);
            $formPosition++;
        }

        $fields = [
            'genus',
            'habitat',
            'shape',
            'color',
            'female_rate',
            'capture_rate',
            'happiness',
            'baby',
            'hatch_steps',
            'growth_rate',
            'forms_switchable',
            'forms_note',
            'name',
            'default',
            'height',
            'weight',
            'experience',
            'types',
            'egg_groups',
            'mega',
            'stats',
            'abilities',
            'wild_held_items',
            'flavor_text',
        ];
        $fields = array_intersect($fields, array_keys($sourceData));
        /** @var Pokemon $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData, $fields);

        return $destinationData;
    }

    protected function transformForm(
        array $sourceData,
        PokemonForm $destinationData,
        VersionGroup $versionGroup
    ): PokemonForm {
        // Pokeathlon stats
        if (isset($sourceData['pokeathlon_stats'])) {
            $pokeathlonStats = [];
            foreach ($sourceData['pokeathlon_stats'] as $pokeathlonStat => $statData) {
                /** @var \App\Entity\PokeathlonStat $pokeathlonStat */
                $pokeathlonStat = $this->referenceStore->get(PokeathlonStat::class, ['identifier' => $pokeathlonStat]);
                $formPokeathlonStat = $destinationData->getPokeathlonStatData(
                        $pokeathlonStat
                    ) ?? new PokemonFormPokeathlonStat();
                $formPokeathlonStat->setPokeathlonStat($pokeathlonStat)
                    ->setBaseValue($statData['base_value'])
                    ->setRange(Range::fromString($statData['range']));
                $pokeathlonStats[] = $formPokeathlonStat;
            }
            $sourceData['pokeathlon_stats'] = $pokeathlonStats;
        }

        // Sprites
        if (isset($sourceData['sprites'])) {
            foreach ($sourceData['sprites'] as &$sprite) {
                $sprite = new PokemonSprite($sprite);
            }
            unset($sprite);
        }

        // Art
        if (isset($sourceData['art'])) {
            foreach ($sourceData['art'] as &$art) {
                $art = new PokemonArt($art);
            }
            unset($art);
        }

        /** @var PokemonForm $destinationData */
        $destinationData = $this->mergeProperties($sourceData, $destinationData);

        return $destinationData;
    }

    /**
     * {@inheritdoc}
     */
    public function defaultResult()
    {
        return new \App\Entity\PokemonSpecies();
    }
}
