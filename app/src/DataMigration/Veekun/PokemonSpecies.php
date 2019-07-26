<?php

namespace App\DataMigration\Veekun;

use App\DataMigration\AbstractDoctrineDataMigration;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use DragoonBoots\A2B\Annotations\DataMigration;
use DragoonBoots\A2B\Annotations\IdField;
use DragoonBoots\A2B\DataMigration\DataMigrationInterface;
use DragoonBoots\A2B\DataMigration\MigrationReferenceStoreInterface;
use DragoonBoots\A2B\Drivers\Destination\YamlDestinationDriver;
use DragoonBoots\A2B\Drivers\DestinationDriverInterface;
use DragoonBoots\A2B\Drivers\Source\DbalSourceDriver;
use DragoonBoots\A2B\Drivers\SourceDriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Pokemon Species, Pokemon, and Pokemon Form migration.
 *
 * @DataMigration(
 *     name="Pokemon",
 *     group="Veekun",
 *     source="veekun",
 *     sourceIds={@IdField(name="id")},
 *     destination="yaml:///%kernel.project_dir%/resources/data/pokemon",
 *     destinationIds={@IdField(name="identifier", type="string")}
 * )
 */
class PokemonSpecies extends AbstractDoctrineDataMigration implements DataMigrationInterface
{

    /**
     * @var Statement
     */
    protected $pokedexNumbersData;

    /**
     * @var Statement
     */
    protected $flavorTextData;

    /**
     * @var Statement
     */
    protected $evolutionData;

    /**
     * A map of locations where evolution might happen and the version groups
     * they appear in.
     *
     * @var array
     */
    protected $evolutionLocationVersionGroups;

    /**
     * A map of species where that affect evolution and the version groups
     * they appear in.
     *
     * @var array
     */
    protected $evolutionSpeciesVersionGroups;

    /**
     * @var Statement
     */
    protected $pokemonData;

    /**
     * @var Statement
     */
    protected $abilityData;

    /**
     * @var Statement
     */
    protected $wildHeldItemsData;

    /**
     * @var Statement
     */
    protected $statsData;

    /**
     * @var Statement
     */
    protected $formData;

    /**
     * @var Statement
     */
    protected $pokathlonStatData;

    /**
     * Map version groups to their member versions
     *
     * @var array
     */
    protected $versionGroupVersions;

    /**
     * Map version groups to their pokedexes
     *
     * @var array
     */
    protected $versionGroupPokedexes;

    /**
     * @var array
     */
    protected $oldMediaVersionGroups;

    /**
     * A list of version groups that don't have a given feature
     */
    protected $featureExclusions = [
        'color' => [
            'red-blue',
            'yellow',
            'gold-silver',
            'crystal',
            'firered-leafgreen',
            'colosseum',
            'xd',
            'diamond-pearl',
            'platinum',
            'heartgold-soulsilver',
        ],
        'shape' => [
            'red-blue',
            'yellow',
            'gold-silver',
            'crystal',
            'ruby-sapphire',
            'emerald',
            'colosseum',
            'xd',
            'firered-leafgreen',
        ],
        'forms' => [
            'red-blue',
            'yellow',
        ],
        'breeding' => [
            'red-blue',
            'yellow',
        ],
        'genders' => [
            'red-blue',
            'yellow',
        ],
        'happiness' => [
            'red-blue',
            'yellow',
        ],
        'icons' => [
            'red-blue',
            'yellow',
            'gold-silver',
            'crystal',
        ],
        'mega' => [
            'red-blue',
            'yellow',
            'gold-silver',
            'crystal',
            'ruby-sapphire',
            'emerald',
            'colosseum',
            'xd',
            'firered-leafgreen',
            'diamond-pearl',
            'platinum',
            'heartgold-soulsilver',
            'black-white',
            'black-2-white-2',
        ],
    ];

    /**
     * @var string
     */
    protected $mediaPath;

    /**
     * PokemonSpecies constructor.
     *
     * @param MigrationReferenceStoreInterface $referenceStore
     * @param PropertyAccessorInterface $propertyAccess
     * @param ContainerInterface $container
     */
    public function __construct(
        MigrationReferenceStoreInterface $referenceStore,
        PropertyAccessorInterface $propertyAccess,
        ContainerInterface $container
    ) {
        parent::__construct($referenceStore, $propertyAccess);

        $this->mediaPath = $container->getParameter('kernel.project_dir').'/assets/static/pokemon';
    }

    /**
     * {@inheritdoc}
     * @param DbalSourceDriver $sourceDriver
     */
    public function configureSource(SourceDriverInterface $sourceDriver)
    {
        $sourceDriver->setStatement(
            <<<SQL
SELECT "pokemon_species"."id",
       "pokemon_species"."identifier",
       "pokemon_species_names"."name",
       "pokemon_species_names"."genus",
       "group_concat"(DISTINCT "version_groups"."identifier") AS "version_groups",
       "evolves_from_species"."identifier" || '/' || "evolves_from_pokemon"."identifier" AS "evolution_parent",
       "pokemon_colors"."identifier" AS "color",
       "pokemon_shapes"."identifier" AS "shape",
       "pokemon_habitats"."identifier" AS "habitat",
       CASE
           WHEN "pokemon_species"."gender_rate" >= 0 THEN
               (cast("pokemon_species"."gender_rate" AS FLOAT) / 8) * 100
           ELSE NULL
       END AS "female_rate",
       "pokemon_species"."capture_rate",
       "pokemon_species"."base_happiness" AS "happiness",
       "pokemon_species"."is_baby" AS "baby",
       "pokemon_species"."hatch_counter" AS "hatch_steps",
       "growth_rates"."identifier" AS "growth_rate",
       "pokemon_species"."forms_switchable",
       "pokemon_species"."order" AS "position",
       "pokemon_species_prose"."form_description" AS "forms_note",
       "pal_park_areas"."identifier" AS "pal_park_area",
       "pal_park"."rate" AS "pal_park_rate",
       "pal_park"."base_score" AS "pal_park_score"
FROM "pokemon_species"
     JOIN "version_groups"
          ON "pokemon_species"."generation_id" <= "version_groups"."generation_id"
     LEFT OUTER JOIN "pokemon_species" "evolves_from_species"
                     ON "pokemon_species"."evolves_from_species_id" = "evolves_from_species"."id"
     LEFT OUTER JOIN "pokemon" "evolves_from_pokemon"
                     ON "evolves_from_species"."id" = "evolves_from_pokemon"."species_id"
                         AND "evolves_from_pokemon"."is_default" = 1
     LEFT OUTER JOIN "pokemon_colors"
                     ON "pokemon_species"."color_id" = "pokemon_colors"."id"
     LEFT OUTER JOIN "pokemon_shapes"
                     ON "pokemon_species"."shape_id" = "pokemon_shapes"."id"
     LEFT OUTER JOIN "pokemon_habitats"
                     ON "pokemon_species"."habitat_id" = "pokemon_habitats"."id"
     JOIN "growth_rates"
          ON "pokemon_species"."growth_rate_id" = "growth_rates"."id"
     JOIN "pokemon_species_names"
          ON "pokemon_species"."id" = "pokemon_species_names"."pokemon_species_id"
     LEFT OUTER JOIN "pokemon_species_prose"
                     ON "pokemon_species"."id" = "pokemon_species_prose"."pokemon_species_id"
     LEFT OUTER JOIN "pal_park" ON "pokemon_species"."id" = "pal_park"."species_id"
     LEFT OUTER JOIN "pal_park_areas" ON "pal_park"."area_id" = "pal_park_areas"."id"
WHERE "pokemon_species_names"."local_language_id" = 9
  AND ("pokemon_species_prose"."local_language_id" = 9 OR "pokemon_species_prose"."local_language_id" IS NULL)
GROUP BY "pokemon_species"."id";
SQL
        );

        $sourceDriver->setCountStatement(
            <<<SQL
SELECT count(*)
FROM "pokemon_species";
SQL
        );

        $this->pokedexNumbersData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "pokedexes"."identifier" AS "pokedex",
       "pokemon_dex_numbers"."pokedex_number" AS "number"
FROM "pokemon_dex_numbers"
     JOIN "pokedexes"
          ON "pokemon_dex_numbers"."pokedex_id" = "pokedexes"."id"
WHERE "species_id" = :species
ORDER BY ("pokedexes"."identifier" = 'national') DESC;
SQL
        );

        $versionGroupPokedexes = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier" AS "version_group",
       'national,' || group_concat(DISTINCT "pokedexes"."identifier") AS "pokedexes"
FROM "pokedex_version_groups"
     JOIN "pokedexes"
          ON "pokedex_version_groups"."pokedex_id" = "pokedexes"."id"
     JOIN "version_groups"
          ON "pokedex_version_groups"."version_group_id" = "version_groups"."id"
GROUP BY "version_groups"."id";
SQL
        );
        $versionGroupPokedexes->execute();
        foreach ($versionGroupPokedexes as $row) {
            $this->versionGroupPokedexes[$row['version_group']] = explode(',', $row['pokedexes']);
        }

        $this->flavorTextData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "versions"."identifier" AS "version",
       "pokemon_species_flavor_text"."flavor_text"
FROM "pokemon_species_flavor_text"
     JOIN "versions"
          ON "pokemon_species_flavor_text"."version_id" = "versions"."id"
     JOIN "version_groups"
          ON "versions"."version_group_id" = "version_groups"."id"
WHERE "species_id" = :species
  AND "language_id" = 9
ORDER BY "version_groups"."order";
SQL
        );

        $this->evolutionData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "evolution_triggers"."identifier" AS "trigger",
       "trigger_items"."identifier" AS "trigger_item",
       "pokemon_evolution"."minimum_level",
       "genders"."identifier" AS "gender",
       "locations"."identifier" AS "location",
       "held_items"."identifier" AS "held_item",
       "pokemon_evolution"."time_of_day",
       "moves"."identifier" AS "known_move",
       "known_move_types"."identifier" AS "known_move_type",
       "pokemon_evolution"."minimum_happiness",
       "pokemon_evolution"."minimum_beauty",
       "pokemon_evolution"."minimum_affection",
       "pokemon_evolution"."relative_physical_stats" AS "physical_stats_difference",
       "party_species"."identifier" AS "party_species",
       "party_types"."identifier" AS "party_type",
       "trade_species"."identifier" AS "traded_for_species",
       CASE
           WHEN "pokemon_evolution"."needs_overworld_rain" = 1 THEN
               'rain'
           ELSE NULL
       END AS "overworld_weather",
       nullif("pokemon_evolution"."turn_upside_down", 0) AS "console_inverted"
FROM "pokemon_evolution"
     JOIN "evolution_triggers"
          ON "pokemon_evolution"."evolution_trigger_id" = "evolution_triggers"."id"
     LEFT OUTER JOIN "items" "trigger_items"
                     ON "pokemon_evolution"."trigger_item_id" = "trigger_items"."id"
     LEFT OUTER JOIN "genders"
                     ON "pokemon_evolution"."gender_id" = "genders"."id"
     LEFT OUTER JOIN "locations"
                     ON "pokemon_evolution"."location_id" = "locations"."id"
     LEFT OUTER JOIN "items" "held_items"
                     ON "pokemon_evolution"."held_item_id" = "held_items"."id"
     LEFT OUTER JOIN "moves"
                     ON "pokemon_evolution"."known_move_id" = "moves"."id"
     LEFT OUTER JOIN "types" "known_move_types"
                     ON "pokemon_evolution"."known_move_type_id" = "known_move_types"."id"
     LEFT OUTER JOIN "pokemon_species" "party_species"
                     ON "pokemon_evolution"."party_species_id" = "party_species"."id"
     LEFT OUTER JOIN "types" "party_types"
                     ON "pokemon_evolution"."party_type_id" = "party_types"."id"
     LEFT OUTER JOIN "pokemon_species" "trade_species"
                     ON "pokemon_evolution"."trade_species_id" = "trade_species"."id"
WHERE "evolved_species_id" = :species;
SQL
        );

        $evolutionLocationVersionGroups = $sourceDriver->getConnection()
            ->prepare(
                <<<SQL
SELECT "locations"."identifier",
       group_concat(DISTINCT "version_groups"."identifier") AS "version_groups"
FROM "locations"
     JOIN "pokemon_evolution"
          ON "locations"."id" = "pokemon_evolution"."location_id"
     JOIN "regions"
          ON "locations"."region_id" = "regions"."id"
     JOIN "version_group_regions"
          ON "regions"."id" = "version_group_regions"."region_id"
     JOIN "version_groups"
          ON "version_group_regions"."version_group_id" = "version_groups"."id"
GROUP BY "locations"."id"
SQL
            );
        $evolutionLocationVersionGroups->execute();
        $this->evolutionLocationVersionGroups = [];
        foreach ($evolutionLocationVersionGroups as $evolutionLocationVersionGroup) {
            $this->evolutionLocationVersionGroups[$evolutionLocationVersionGroup['identifier']] = explode(
                ',',
                $evolutionLocationVersionGroup['version_groups']
            );
        }

        $evolutionSpeciesVersionGroups = $sourceDriver->getConnection()
            ->prepare(
                <<<SQL
SELECT "pokemon_species"."identifier",
       group_concat(DISTINCT "version_groups"."identifier") AS "version_groups"
FROM "pokemon_evolution"
     JOIN "pokemon_species"
          ON "pokemon_evolution"."party_species_id" = "pokemon_species"."id"
     JOIN "pokemon"
          ON "pokemon_species"."id" = "pokemon"."species_id"
              AND "pokemon"."is_default" = 1
     JOIN "pokemon_forms"
          ON "pokemon"."id" = "pokemon_forms"."pokemon_id" AND "pokemon_forms"."is_default" = 1
     JOIN "pokemon_form_generations"
          ON "pokemon_forms"."id" = "pokemon_form_generations"."pokemon_form_id"
     JOIN "version_groups"
          ON "pokemon_form_generations"."generation_id" <= "version_groups"."generation_id"
GROUP BY "pokemon_species"."id"
UNION
SELECT "pokemon_species"."identifier",
       group_concat(DISTINCT "version_groups"."identifier") AS "version_groups"
FROM "pokemon_evolution"
     JOIN "pokemon_species"
          ON "pokemon_evolution"."trade_species_id" = "pokemon_species"."id"
     JOIN "pokemon"
          ON "pokemon_species"."id" = "pokemon"."species_id"
              AND "pokemon"."is_default" = 1
     JOIN "pokemon_forms"
          ON "pokemon"."id" = "pokemon_forms"."pokemon_id" AND "pokemon_forms"."is_default" = 1
     JOIN "pokemon_form_generations"
          ON "pokemon_forms"."id" = "pokemon_form_generations"."pokemon_form_id"
     JOIN "version_groups"
          ON "pokemon_form_generations"."generation_id" <= "version_groups"."generation_id"
GROUP BY "pokemon_species"."id"
UNION
SELECT "evolution_parent_species"."identifier" || '/' || "evolution_parent_pokemon"."identifier" AS "identifier",
       group_concat(DISTINCT "version_groups"."identifier") AS "version_groups"
FROM "pokemon_species"
     JOIN "pokemon_species" "evolution_parent_species"
          ON "pokemon_species"."evolves_from_species_id" = "evolution_parent_species"."id"
     JOIN "pokemon" "evolution_parent_pokemon"
          ON "evolution_parent_species"."id" = "evolution_parent_pokemon"."species_id"
              AND "evolution_parent_pokemon"."is_default" = 1
     JOIN "pokemon_forms"
          ON "evolution_parent_pokemon"."id" = "pokemon_forms"."pokemon_id" AND "pokemon_forms"."is_default" = 1
     JOIN "pokemon_form_generations"
          ON "pokemon_forms"."id" = "pokemon_form_generations"."pokemon_form_id"
     JOIN "version_groups"
          ON "pokemon_form_generations"."generation_id" <= "version_groups"."generation_id"
GROUP BY "evolution_parent_pokemon"."id"
SQL
            );
        $evolutionSpeciesVersionGroups->execute();
        $this->evolutionSpeciesVersionGroups = [];
        foreach ($evolutionSpeciesVersionGroups as $evolutionSpeciesVersionGroup) {
            $this->evolutionSpeciesVersionGroups[$evolutionSpeciesVersionGroup['identifier']] = explode(
                ',',
                $evolutionSpeciesVersionGroup['version_groups']
            );
        }

        $this->pokemonData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "pokemon"."id",
       "pokemon"."identifier",
       CASE
           WHEN "pokemon"."is_default" = 1 THEN
               "pokemon_species_names"."name"
           ELSE
               coalesce("pokemon_form_names"."pokemon_name", "pokemon_species_names"."name")
       END AS "name",
       "pokemon"."is_default" AS "default",
       "group_concat"(DISTINCT "version_groups"."identifier") AS "version_groups",
       "pokemon"."height",
       "pokemon"."weight",
       "pokemon"."base_experience" AS "experience",
       group_concat(DISTINCT "types"."identifier") AS "types",
       group_concat(DISTINCT "egg_groups"."identifier") AS "egg_groups",
       "pokemon_forms"."is_mega" AS "mega"
FROM "pokemon"
     JOIN "pokemon_species"
          ON "pokemon"."species_id" = "pokemon_species"."id"
     JOIN "pokemon_species_names"
          ON "pokemon_species"."id" = "pokemon_species_names"."pokemon_species_id"
     JOIN "pokemon_forms"
          ON "pokemon"."id" = "pokemon_forms"."pokemon_id" AND "pokemon_forms"."is_default" = 1
     JOIN "pokemon_form_generations"
          ON "pokemon_forms"."id" = "pokemon_form_generations"."pokemon_form_id"
     JOIN "version_groups"
          ON "pokemon_form_generations"."generation_id" <= "version_groups"."generation_id"
     LEFT OUTER JOIN "pokemon_form_names"
                     ON "pokemon_forms"."id" = "pokemon_form_names"."pokemon_form_id"
     JOIN "pokemon_types"
          ON "pokemon"."id" = "pokemon_types"."pokemon_id"
     JOIN "types"
          ON "pokemon_types"."type_id" = "types"."id"
     LEFT OUTER JOIN "pokemon_egg_groups"
                     ON "pokemon_species"."id" = "pokemon_egg_groups"."species_id"
     LEFT OUTER JOIN "egg_groups"
                     ON "pokemon_egg_groups"."egg_group_id" = "egg_groups"."id"
WHERE "pokemon"."species_id" = :species
  AND ("pokemon_form_names"."local_language_id" = 9 OR "pokemon_form_names"."local_language_id" IS NULL)
  AND "pokemon_species_names"."local_language_id" = 9
GROUP BY "pokemon"."id"
ORDER BY "pokemon"."order", "pokemon_types"."slot";
SQL
        );

        $this->abilityData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "abilities"."identifier" AS "identifier",
       "is_hidden" AS "hidden",
       "slot" AS "position",
       group_concat(DISTINCT "version_groups"."identifier") AS "version_groups"
FROM "pokemon_abilities"
     JOIN "abilities"
          ON "pokemon_abilities"."ability_id" = "abilities"."id"
     JOIN "version_groups"
          ON "abilities"."generation_id" <= "version_groups"."generation_id"
WHERE "pokemon_id" = :pokemon
GROUP BY "abilities"."id"
ORDER BY "pokemon_abilities"."slot";
SQL
        );

        $this->wildHeldItemsData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "versions"."identifier" AS "version",
       "items"."identifier" AS "item",
       "pokemon_items"."rarity" AS "chance"
FROM "pokemon_items"
     JOIN "versions"
          ON "pokemon_items"."version_id" = "versions"."id"
     JOIN "version_groups"
          ON "versions"."version_group_id" = "version_groups"."id"
     JOIN "items"
          ON "pokemon_items"."item_id" = "items"."id"
WHERE "pokemon_id" = :pokemon
ORDER BY "version_groups"."order";
SQL
        );

        $this->statsData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "stats"."identifier" AS "stat",
       "pokemon_stats"."base_stat" AS "base_value",
       "pokemon_stats"."effort" AS "effort_change"
FROM "pokemon_stats"
     JOIN "stats"
          ON "pokemon_stats"."stat_id" = "stats"."id"
WHERE "pokemon_stats"."pokemon_id" = :pokemon
ORDER BY "stats"."game_index";
SQL
        );

        $this->formData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "pokemon_forms"."id",
       "pokemon_forms"."identifier",
       group_concat(DISTINCT "version_groups"."identifier") AS "version_groups",
       coalesce("pokemon_form_names"."pokemon_name", "pokemon_species_names"."name") AS "name",
       coalesce("pokemon_form_names"."form_name", 'Default Form') AS "form_name",
       coalesce("pokemon_forms"."form_identifier", 'default') AS "form_identifier",
       "pokemon_forms"."is_default" AS "default",
       "is_battle_only" AS "battle_only"
FROM "pokemon_forms"
     LEFT OUTER JOIN "pokemon_form_names"
                     ON "pokemon_forms"."id" = "pokemon_form_names"."pokemon_form_id"
     JOIN "pokemon_form_generations"
          ON "pokemon_forms"."id" = "pokemon_form_generations"."pokemon_form_id"
     JOIN "version_groups"
          ON "pokemon_form_generations"."generation_id" <= "version_groups"."generation_id"
     JOIN "pokemon"
          ON "pokemon_forms"."pokemon_id" = "pokemon"."id"
     JOIN "pokemon_species_names"
          ON "pokemon"."species_id" = "pokemon_species_names"."pokemon_species_id"
WHERE "pokemon_forms"."pokemon_id" = :pokemon
  AND ("pokemon_form_names"."local_language_id" = 9 OR "pokemon_form_names"."local_language_id" IS NULL)
  AND "pokemon_species_names"."local_language_id" = 9
GROUP BY "pokemon_forms"."id"
ORDER BY "pokemon_forms"."order";
SQL
        );

        $this->pokathlonStatData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "pokeathlon_stats"."identifier" AS "pokeathlon_stat",
       "pokemon_form_pokeathlon_stats"."minimum_stat" AS "min",
       "pokemon_form_pokeathlon_stats"."maximum_stat" AS "max",
       "pokemon_form_pokeathlon_stats"."base_stat" AS "base_value"
FROM "pokemon_form_pokeathlon_stats"
     JOIN "pokeathlon_stats"
          ON "pokemon_form_pokeathlon_stats"."pokeathlon_stat_id" = "pokeathlon_stats"."id"
WHERE "pokemon_form_pokeathlon_stats"."pokemon_form_id" = :form
ORDER BY "pokeathlon_stats"."id";
SQL
        );

        $versionGroupsData = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier" AS "version_group",
       group_concat(DISTINCT "versions"."identifier") AS "versions"
FROM "versions"
     JOIN "version_groups"
          ON "versions"."version_group_id" = "version_groups"."id"
GROUP BY "version_groups"."id";
SQL
        );
        $versionGroupsData->execute();
        foreach ($versionGroupsData as $row) {
            $this->versionGroupVersions[$row['version_group']] = explode(',', $row['versions']);
        }

        $oldMediaVersionGroups = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier"
FROM "version_groups"
WHERE "generation_id" <= 5;
SQL
        );
        $oldMediaVersionGroups->execute();
        $this->oldMediaVersionGroups = $oldMediaVersionGroups->fetchAll(FetchMode::COLUMN);

        $noFootPrintsVersionGroups = $sourceDriver->getConnection()->prepare(
            <<<SQL
SELECT "version_groups"."identifier"
FROM "version_groups"
WHERE "generation_id" < 2
AND "generation_id" > 5
SQL
        );
        $noFootPrintsVersionGroups->execute();
        $this->featureExclusions['footprints'] = $noFootPrintsVersionGroups->fetchAll(FetchMode::COLUMN);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($sourceData, $destinationData)
    {
        $speciesId = $sourceData['id'];
        unset($sourceData['id']);
        $destinationData['identifier'] = $sourceData['identifier'];
        unset($sourceData['identifier']);

        $speciesVersionGroups = explode(',', $sourceData['version_groups']);
        unset($sourceData['version_groups']);
        $intFields = [
            'female_rate',
            'capture_rate',
            'happiness',
            'hatch_steps',
            'position',
        ];
        $sourceData = $this->convertToInts($sourceData, $intFields);
        $sourceData['baby'] = (bool)$sourceData['baby'];
        $sourceData['forms_switchable'] = (bool)$sourceData['forms_switchable'];
        $sourceData = $this->removeNulls($sourceData);

        // Clean up pal park data
        if (isset($sourceData['pal_park_area'], $sourceData['pal_park_rate'], $sourceData['pal_park_score'])) {
            $sourceData['pal_park'] = [
                'area' => $sourceData['pal_park_area'],
                'rate' => (int)$sourceData['pal_park_rate'],
                'score' => (int)$sourceData['pal_park_score'],
            ];
            unset($sourceData['pal_park_area'], $sourceData['pal_park_rate'], $sourceData['pal_park_score']);
        }

        // Map flavor text by version
        $this->flavorTextData->execute(['species' => $speciesId]);
        $flavorTextData = $this->flavorTextData->fetchAll(FetchMode::ASSOCIATIVE);
        $flavorTextData = array_combine(
            array_column($flavorTextData, 'version'),
            array_column($flavorTextData, 'flavor_text')
        );

        // Map pokedex numbers by pokedex
        $this->pokedexNumbersData->execute(['species' => $speciesId]);
        $numbersData = $this->pokedexNumbersData->fetchAll(FetchMode::ASSOCIATIVE);
        $pokedexNumbersData = array_combine(
            array_column($numbersData, 'pokedex'),
            array_column($numbersData, 'number')
        );
        $pokedexNumbersData = $this->convertToInts($pokedexNumbersData, array_keys($pokedexNumbersData));

        // Map evolution data by trigger
        $this->evolutionData->execute(['species' => $speciesId]);
        $evolutionData = $this->evolutionData->fetchAll(FetchMode::ASSOCIATIVE);
        $evolutionConditions = [];
        $intFields = [
            'minimum_level',
            'minimum_happiness',
            'minimum_beauty',
            'minimum_affection',
            'physical_stats_difference',
        ];
        foreach ($evolutionData as $evolutionDatum) {
            $trigger = $evolutionDatum['trigger'];
            unset($evolutionDatum['trigger']);
            $evolutionCondition = $this->removeNulls($evolutionDatum);
            $evolutionCondition = $this->convertToInts($evolutionCondition, $intFields);
            if (isset($evolutionCondition['console_inverted'])) {
                $evolutionCondition['console_inverted'] = (bool)$evolutionCondition['console_inverted'];
            }
            $evolutionConditions[$trigger][] = $evolutionCondition;
        }

        // Move data that veekun puts with the species into pokemon data
        $speciesFields = [
            'name',
            'position',
        ];
        $speciesDataSubset = [];
        foreach ($sourceData as $k => $v) {
            if (!in_array($k, $speciesFields)) {
                $speciesDataSubset[$k] = $v;
                unset($sourceData[$k]);
            }
        }

        // Map pokemon by version group.
        $this->pokemonData->execute(['species' => $speciesId]);
        $pokemonData = $this->pokemonData->fetchAll(FetchMode::ASSOCIATIVE);
        $versionGroupPokemonData = [];
        foreach ($pokemonData as $pokemonSourceData) {
            $pokemonVersionGroups = explode(',', $pokemonSourceData['version_groups']);
            unset($pokemonSourceData['version_groups']);
            $pokemonIdentifier = $pokemonSourceData['identifier'];
            unset($pokemonSourceData['identifier']);
            $pokemonId = $pokemonSourceData['id'];
            unset($pokemonSourceData['id']);

            // Map forms by version group
            $this->formData->execute(['pokemon' => $pokemonId]);
            $formData = $this->formData->fetchAll(FetchMode::ASSOCIATIVE);
            $versionGroupFormData = [];
            foreach ($formData as $formSourceData) {
                $formId = $formSourceData['id'];
                unset($formSourceData['id']);
                $formVersionGroups = explode(',', $formSourceData['version_groups']);
                unset($formSourceData['version_groups']);
                $formIdentifier = $formSourceData['identifier'];
                unset($formSourceData['identifier']);
                $formSourceData['default'] = (bool)$formSourceData['default'];
                $formSourceData['battle_only'] = (bool)$formSourceData['battle_only'];

                foreach ($formVersionGroups as $formVersionGroup) {
                    $versionGroupData = $formSourceData;

                    if ($formVersionGroup == 'heartgold-soulsilver') {
                        // Add Pokeathlon
                        $this->pokathlonStatData->execute(['form' => $formId]);
                        foreach ($this->pokathlonStatData->fetchAll(
                            FetchMode::ASSOCIATIVE
                        ) as $pokeathlonSourceData) {
                            $pokeathlonStat = $pokeathlonSourceData['pokeathlon_stat'];
                            unset($pokeathlonSourceData['pokeathlon_stat']);
                            $pokeathlonSourceData['range'] = $this->buildRangeString(
                                $pokeathlonSourceData['min'],
                                $pokeathlonSourceData['max']
                            );
                            unset($pokeathlonSourceData['min'], $pokeathlonSourceData['max']);
                            $pokeathlonSourceData = $this->convertToInts($pokeathlonSourceData, ['base_value']);

                            $versionGroupData['pokeathlon_stats'][$pokeathlonStat] = $pokeathlonSourceData;
                        }
                    }

                    // Media
                    // Icons
                    if (!in_array($formVersionGroup, $this->featureExclusions['icons'], true)) {
                        $versionGroupData['icon'] = sprintf(
                            '%s-%s.png',
                            $destinationData['identifier'],
                            $versionGroupData['form_identifier']
                        );
                        if (in_array($formVersionGroup, $this->oldMediaVersionGroups, true)) {
                            $versionGroupData['icon'] = 'gen5/'.$versionGroupData['icon'];
                        }
                    }
                    // Sprites
                    // Check to see what sprites are available.
                    $spriteCheckDir = sprintf('%s/sprite/%s', $this->mediaPath, $formVersionGroup);
                    if (is_dir($spriteCheckDir)) {
                        $spriteFinder = new Finder();
                        $spriteFinder->files()
                            ->in($spriteCheckDir)
                            ->name(
                                sprintf(
                                    '`^%s-%s\.(?:png|webm)$`',
                                    preg_quote($destinationData['identifier'], '`'),
                                    preg_quote($versionGroupData['form_identifier'], '`')
                                )
                            );
                        foreach ($spriteFinder->getIterator() as $spriteFileInfo) {
                            $versionGroupData['sprites'][] = sprintf(
                                '%s/%s',
                                $formVersionGroup,
                                $spriteFileInfo->getRelativePathname()
                            );
                        }
                        if (isset($versionGroupData['sprites'])) {
                            $versionGroupData['sprites'] = array_reverse($versionGroupData['sprites']);
                        }
                    }
                    // Art
                    // Check to see what art is available
                    $artCheckDir = sprintf('%s/art', $this->mediaPath);
                    $artFinder = new Finder();
                    $artFinder->files()
                        ->in($artCheckDir)
                        ->name(
                            sprintf(
                                '%s-%s.png',
                                preg_quote($destinationData['identifier'], '`'),
                                preg_quote($versionGroupData['form_identifier'], '`')
                            )
                        );
                    foreach ($artFinder->getIterator() as $artFileInfo) {
                        $versionGroupData['art'][] = $artFileInfo->getRelativePathname();
                    }
                    if (isset($versionGroupData['art'])) {
                        $versionGroupData['art'] = array_reverse($versionGroupData['art']);
                    }
                    // Footprints
                    if (!in_array($formVersionGroup, $this->featureExclusions['footprints'], true)) {
                        $versionGroupData['footprint'] = sprintf('%s.png', $destinationData['identifier']);
                    }
                    // Cries
                    // Try the cry with the form first, then try the default cry.
                    foreach ([$versionGroupData['form_identifier'], 'default'] as $checkFormIdentifier) {
                        $checkCryPath = sprintf(
                            '%s/cry/%s%s-%s.webm',
                            $this->mediaPath,
                            in_array($formVersionGroup, $this->oldMediaVersionGroups, true) ? 'gen5/' : '',
                            $destinationData['identifier'],
                            $checkFormIdentifier
                        );

                        if (is_file($checkCryPath)) {
                            $versionGroupData['cry'] = sprintf(
                                '%s%s-%s.webm',
                                in_array($formVersionGroup, $this->oldMediaVersionGroups, true) ? 'gen5/' : '',
                                $destinationData['identifier'],
                                $checkFormIdentifier
                            );
                            break;
                        }
                    }

                    unset($versionGroupData['form_identifier']);
                    $versionGroupFormData[$formVersionGroup][$formIdentifier] = $versionGroupData;
                }
            }
            unset($formSourceData, $formData);

            $intFields = [
                'height',
                'weight',
                'experience',
            ];
            $pokemonSourceData = $this->convertToInts($pokemonSourceData, $intFields);
            $pokemonSourceData['mega'] = (bool)$pokemonSourceData['mega'];
            $pokemonSourceData['default'] = (bool)$pokemonSourceData['default'];

            // Set types and egg groups
            $pokemonSourceData['types'] = explode(',', $pokemonSourceData['types']);
            if (isset($pokemonSourceData['egg_groups'])) {
                $pokemonSourceData['egg_groups'] = explode(',', $pokemonSourceData['egg_groups']);
            }

            // Map ability data
            $this->abilityData->execute(['pokemon' => $pokemonId]);
            $abilitiesData = $this->abilityData->fetchAll(FetchMode::ASSOCIATIVE);
            $versionGroupAbilityData = [];
            foreach ($abilitiesData as $abilitiesSourceData) {
                $abilityIdentifier = $abilitiesSourceData['identifier'];
                unset($abilitiesSourceData['identifier']);
                $abilityVersionGroups = explode(',', $abilitiesSourceData['version_groups']);
                unset($abilitiesSourceData['version_groups']);
                $abilitiesSourceData = $this->convertToInts($abilitiesSourceData, ['position']);
                $abilitiesSourceData['hidden'] = (bool)$abilitiesSourceData['hidden'];
                foreach ($abilityVersionGroups as $abilityVersionGroup) {
                    if (!isset($versionGroupAbilityData[$abilityVersionGroup])) {
                        $versionGroupAbilityData[$abilityVersionGroup] = [];
                    }
                    $versionGroupAbilityData[$abilityVersionGroup][$abilityIdentifier] = $abilitiesSourceData;
                }
            }
            unset($abilitiesData);

            // Map items the pokemon can be found with in the wild.
            $this->wildHeldItemsData->execute(['pokemon' => $pokemonId]);
            $wildHeldItemsData = $this->wildHeldItemsData->fetchAll(FetchMode::ASSOCIATIVE);
            $versionWildHeldItemsData = [];
            foreach ($wildHeldItemsData as $wildHeldItemsSourceData) {
                $versionWildHeldItemsData[$wildHeldItemsSourceData['version']][$wildHeldItemsSourceData['item']] = (int)$wildHeldItemsSourceData['chance'];
            }
            unset($wildHeldItemsData);

            // Fetch stats data
            $this->statsData->execute(['pokemon' => $pokemonId]);
            $statsData = [];
            foreach ($this->statsData->fetchAll(FetchMode::ASSOCIATIVE) as $statData) {
                $stat = $statData['stat'];
                unset($statData['stat']);
                $statData = $this->convertToInts(
                    $statData,
                    [
                        'base_value',
                        'effort_change',
                    ]
                );
                $statsData[$stat] = $statData;
            }
            $pokemonSourceData['stats'] = $statsData;
            unset($statsData);

            // Merge all data per version group
            foreach ($pokemonVersionGroups as $pokemonVersionGroup) {
                $versionGroupData = array_merge($speciesDataSubset, $pokemonSourceData);

                // Remove data that does not apply to this version group
                if (in_array($pokemonVersionGroup, $this->featureExclusions['color'])) {
                    unset($versionGroupData['color']);
                }
                if (in_array($pokemonVersionGroup, $this->featureExclusions['shape'])) {
                    unset($versionGroupData['shape']);
                }
                if ($pokemonVersionGroup !== 'firered-leafgreen') {
                    unset($versionGroupData['habitat']);
                }
                if (in_array($pokemonVersionGroup, $this->featureExclusions['genders'])) {
                    unset($versionGroupData['female_rate']);
                }
                if (in_array($pokemonVersionGroup, $this->featureExclusions['happiness'])) {
                    unset($versionGroupData['happiness']);
                }
                if (in_array($pokemonVersionGroup, $this->featureExclusions['breeding'])) {
                    unset(
                        $versionGroupData['baby'],
                        $versionGroupData['hatch_steps'],
                        $versionGroupData['egg_groups']
                    );
                }
                if (in_array($pokemonVersionGroup, $this->featureExclusions['forms'])) {
                    unset($versionGroupData['forms_switchable']);
                }
                if (in_array($pokemonVersionGroup, $this->featureExclusions['mega'])) {
                    unset($versionGroupData['mega']);
                }
                if (!in_array($pokemonVersionGroup, ['diamond-pearl', 'platinum', 'heartgold-soulsilver'])) {
                    unset($versionGroupData['pal_park']);
                }

                // Merge in abilities and wild held items
                if (isset($versionGroupAbilityData[$pokemonVersionGroup])) {
                    $versionGroupData['abilities'] = $versionGroupAbilityData[$pokemonVersionGroup];
                }
                foreach ($this->versionGroupVersions[$pokemonVersionGroup] as $version) {
                    if (isset($versionWildHeldItemsData[$version])) {
                        $versionGroupData['wild_held_items'][$version] = $versionWildHeldItemsData[$version];
                    }
                }

                if (isset($versionGroupData['evolution_parent'])
                    && !in_array(
                        $pokemonVersionGroup,
                        $this->evolutionSpeciesVersionGroups[$versionGroupData['evolution_parent']]
                    )) {
                    // This pokemon has no parent evolution in this version.  This is mostly
                    // for baby Pokemon added later.
                    unset($versionGroupData['evolution_parent']);
                    unset($versionGroupData['evolution_conditions']);
                }

                // Prefer data already in the file over veekun's somewhat-incomplete data.
                if (!isset($versionGroupPokemonData[$pokemonVersionGroup][$pokemonIdentifier]['evolution_conditions'])
                    && isset($versionGroupData['evolution_parent'])) {
                    // Check conditions for applicability to this version group.
                    foreach ($evolutionConditions as $trigger => $evolutionConditionSet) {
                        foreach ($evolutionConditionSet as $evolutionCondition) {
                            if (isset($evolutionCondition['location'])
                                && !in_array(
                                    $pokemonVersionGroup,
                                    $this->evolutionLocationVersionGroups[$evolutionCondition['location']]
                                )) {
                                continue;
                            } elseif (isset($evolutionCondition['party_species'])
                                && !in_array(
                                    $pokemonVersionGroup,
                                    $this->evolutionSpeciesVersionGroups[$evolutionCondition['party_species']]
                                )) {
                                continue;
                            } elseif (isset($evolutionCondition['traded_for_species'])
                                && !in_array(
                                    $pokemonVersionGroup,
                                    $this->evolutionSpeciesVersionGroups[$evolutionCondition['traded_for_species']]
                                )) {
                                continue;
                            }
                            $versionGroupData['evolution_conditions'][$trigger][] = $evolutionCondition;
                        }
                        $versionGroupData['evolution_conditions'][$trigger] = array_merge(
                            ...
                            $versionGroupData['evolution_conditions'][$trigger]
                        );
                    }
                }

                foreach ($this->versionGroupVersions[$pokemonVersionGroup] as $version) {
                    if (isset($flavorTextData[$version])) {
                        $versionGroupData['flavor_text'][$version] = $flavorTextData[$version];
                    }
                }

                foreach ($versionGroupFormData[$pokemonVersionGroup] as $formIdentifier => $formData) {
                    $versionGroupData['forms'][$formIdentifier] = array_merge(
                        $formData,
                        $destinationData[$pokemonVersionGroup]['pokemon'][$pokemonIdentifier]['forms'][$formIdentifier] ?? []
                    );
                }

                $pokemonFields = array_merge(
                    array_keys($versionGroupData),
                    array_keys($destinationData[$pokemonVersionGroup]['pokemon'][$pokemonIdentifier] ?? [])
                );
                $pokemonFields = array_unique($pokemonFields);
                $pokemonFields = array_diff($pokemonFields, ['forms']);
                $versionGroupPokemonData[$pokemonVersionGroup][$pokemonIdentifier] = $this->arrayMergeOnly(
                    $pokemonFields,
                    $versionGroupData,
                    $destinationData[$pokemonVersionGroup]['pokemon'][$pokemonIdentifier] ?? []
                );
            }
        }
        unset($pokemonSourceData, $pokemonData);

        foreach ($speciesVersionGroups as $versionGroup) {
            $versionGroupData = $sourceData;
            if (isset($this->versionGroupPokedexes[$versionGroup])) {
                foreach ($this->versionGroupPokedexes[$versionGroup] as $pokedex) {
                    if (isset($pokedexNumbersData[$pokedex])) {
                        $versionGroupData['numbers'][$pokedex] = $pokedexNumbersData[$pokedex];
                    }
                }
            }

            $versionGroupData['pokemon'] = $versionGroupPokemonData[$versionGroup];

            $destinationData[$versionGroup] = $this->arrayMergeOnly(
                ['name', 'position', 'numbers'],
                $versionGroupData,
                $destinationData[$versionGroup] ?? []
            );
        }

        return $destinationData;
    }

    /**
     * @param array $fields
     * @param array ...$arrays
     *
     * @return array
     */
    protected function arrayMergeOnly(
        array $fields,
        ...$arrays
    ): array {
        $arrays = array_reverse($arrays);
        $out = array_pop($arrays);
        while ($array = array_pop($arrays)) {
            foreach ($fields as $field) {
                if (isset($array[$field])) {
                    $out[$field] = $array[$field];
                }
            }
        }

        return $out;
    }

    /**
     * {@inheritdoc}
     * @param YamlDestinationDriver $destinationDriver
     */
    public function configureDestination(
        DestinationDriverInterface $destinationDriver
    ) {
        $destinationDriver->setOption(
            'refs',
            [
                'exclude' => [
                    '`.+stats\..+`',
                    '`.+pokeathlon_stats\..+`',
                ],
            ]
        );
    }
}
