<?php

namespace App\Controller;

use App\DataTable\Type\BreedingPokemonTableType;
use App\DataTable\Type\MoveTableType;
use App\DataTable\Type\PokemonHeldItemTableType;
use App\DataTable\Type\PokemonMoveTableType;
use App\DataTable\Type\PokemonTableType;
use App\Entity\EvolutionTrigger;
use App\Entity\LocationInVersionGroup;
use App\Entity\LocationMap;
use App\Entity\Media\RegionMap;
use App\Entity\Pokemon;
use App\Entity\Region;
use App\Entity\Version;
use App\Mechanic\Breeding;
use App\Mechanic\LevelUp;
use App\Repository\EncounterRepository;
use App\Repository\MoveLearnMethodRepository;
use App\Repository\PokemonFormRepository;
use App\Repository\PokemonRepository;
use App\Repository\PokemonSpeciesInVersionGroupRepository;
use App\Repository\PokemonStatRepository;
use App\Repository\PokemonWildHeldItemRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PokemonController
 *
 * @Route("/dex/{versionSlug}/pokemon", name="pokemon_")
 */
class PokemonController extends AbstractDexController
{
    /**
     * @var PokemonSpeciesInVersionGroupRepository
     */
    private $speciesRepo;

    /**
     * @var PokemonRepository
     */
    private $pokemonRepo;

    /**
     * @var PokemonFormRepository
     */
    private $pokemonFormRepo;

    /**
     * @var PokemonWildHeldItemRepository
     */
    private $wildHeldItemRepo;

    /**
     * @var PokemonStatRepository
     */
    private $pokemonStatRepo;

    /**
     * @var MoveLearnMethodRepository
     */
    private $moveLearnMethodRepo;

    /**
     * @var EncounterRepository
     */
    private $encounterRepo;

    /**
     * @var LevelUp
     */
    private $levelUp;

    /**
     * @var Breeding
     */
    private $breeding;

    /**
     * PokemonController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param PokemonSpeciesInVersionGroupRepository $speciesRepo
     * @param PokemonRepository $pokemonRepo
     * @param PokemonFormRepository $pokemonFormRepo
     * @param PokemonWildHeldItemRepository $wildHeldItemRepo
     * @param PokemonStatRepository $pokemonStatRepo
     * @param MoveLearnMethodRepository $moveLearnMethodRepo
     * @param EncounterRepository $encounterRepo
     * @param LevelUp $levelUp
     * @param Breeding $breeding
     */
    public function __construct(
        DataTableFactory $dataTableFactory,
        PokemonSpeciesInVersionGroupRepository $speciesRepo,
        PokemonRepository $pokemonRepo,
        PokemonFormRepository $pokemonFormRepo,
        PokemonWildHeldItemRepository $wildHeldItemRepo,
        PokemonStatRepository $pokemonStatRepo,
        MoveLearnMethodRepository $moveLearnMethodRepo,
        EncounterRepository $encounterRepo,
        LevelUp $levelUp,
        Breeding $breeding
    ) {
        parent::__construct($dataTableFactory);

        $this->speciesRepo = $speciesRepo;
        $this->pokemonRepo = $pokemonRepo;
        $this->pokemonFormRepo = $pokemonFormRepo;
        $this->wildHeldItemRepo = $wildHeldItemRepo;
        $this->pokemonStatRepo = $pokemonStatRepo;
        $this->moveLearnMethodRepo = $moveLearnMethodRepo;
        $this->encounterRepo = $encounterRepo;
        $this->levelUp = $levelUp;
        $this->breeding = $breeding;
    }

    /**
     * @param Request $request
     * @param Version $version
     *
     * @return Response
     *
     * @Route("/", name="index")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function index(
        Request $request,
        Version $version
    ): Response {
        $table = $this->dataTableFactory->createFromType(
            PokemonTableType::class,
            ['version' => $version]
        )->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render(
            'pokemon/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('pokemon_index', ['versionSlug' => '__VERSION__']),
                'pokemon_table' => $table,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $speciesSlug
     * @param string $pokemonSlug
     *
     * @return Response
     *
     * @Route("/{speciesSlug}/{pokemonSlug?}", name="view")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function view(
        Request $request,
        Version $version,
        string $speciesSlug,
        ?string $pokemonSlug
    ): Response {
        $species = $this->speciesRepo->findOneByVersion($speciesSlug, $version);
        if ($species === null) {
            throw new NotFoundHttpException();
        }
        $pokemon = $this->pokemonRepo->findOneBySpecies($species, $version, $pokemonSlug);
        if ($pokemon === null) {
            throw new NotFoundHttpException();
        }
        $formSlug = $request->query->get('form');
        $form = $this->pokemonFormRepo->findOneByPokemon($pokemon, $version, $formSlug);
        if ($form === null) {
            throw new NotFoundHttpException();
        }

        // Can breed with table
        $canBreedWithTable = $this->dataTableFactory->createFromType(
            BreedingPokemonTableType::class,
            [
                'version' => $version,
                'pokemon' => $pokemon,
            ]
        )->handleRequest($request);
        if ($canBreedWithTable->isCallback()) {
            return $canBreedWithTable->getResponse();
        }

        // Held items table
        $heldItemsTable = $this->dataTableFactory->createFromType(
            PokemonHeldItemTableType::class,
            [
                'version' => $version,
                'pokemon' => $pokemon,
            ]
        )->handleRequest($request);
        if ($heldItemsTable->isCallback()) {
            return $heldItemsTable->getResponse();
        }

        // Moves table
        $moveLearnMethods = $this->moveLearnMethodRepo->findUsedMethodsForPokemon($pokemon);
        /** @var MoveTableType[] $moveTables */
        $moveTables = [];
        foreach ($moveLearnMethods as $learnMethod) {
            $moveTable = $this->dataTableFactory->createFromType(
                PokemonMoveTableType::class,
                [
                    'version' => $version,
                    'pokemon' => $pokemon,
                    'learnMethod' => $learnMethod,
                ]
            )->handleRequest($request);
            if ($moveTable->isCallback()) {
                return $moveTable->getResponse();
            }
            $moveTables[$learnMethod->getSlug()] = $moveTable;
        }

        // Encounters table
        //        $encounterTable = $this->dataTableFactory->createFromType(
        //            PokemonEncounterTableType::class,
        //            [
        //                'version' => $version,
        //                'pokemon' => $pokemon,
        //            ]
        //        )->handleRequest($request);
        //        if ($encounterTable->isCallback()) {
        //            return $encounterTable->getResponse();
        //        }

        // Encounters
        $encounters = $this->encounterRepo->findByPokemon($pokemon, $version);
        // Unique locations
        $locations = [];
        // Multi-level map:
        // location_id => [
        //   'location' => Location entity,
        //   'areas' => [
        //     location_area_id => [
        //       'area' => Area entity,
        //       'encounters' => [
        //         Encounter entity
        //         ...
        //       ],
        //     ],
        //     ...
        //   ],
        // ]
        $encountersByLocation = [];
        foreach ($encounters as $encounter) {
            $locationArea = $encounter->getLocationArea();
            $location = $locationArea->getLocation();
            $locations[$location->getId()] = $location;

            // Organize encounters for display
            $encountersByLocation[$location->getId()]['areas'][$locationArea->getId()]['encounters'][] = $encounter;
        }
        $encounterMaps = [];
        /** @var RegionMap[] $encounterMapsUse */
        $encounterMapsUse = [];
        /** @var LocationInVersionGroup[] $encountersNotHighlighted */
        $encountersNotHighlighted = [];
        foreach ($locations as $location) {
            $map = $this->findLocationMap($location);
            if ($map === null) {
                $encountersNotHighlighted[] = $location;
                continue;
            }
            $encounterMaps[$map->getMap()->getSlug()][] = $map;
            $encounterMapsUse[$map->getMap()->getSlug()] = $map->getMap();

            // Add location entities to organized encounter list
            $encountersByLocation[$location->getId()]['location'] = $location;
            foreach ($location->getAreas() as $area) {
                $encountersByLocation[$location->getId()]['areas'][$area->getId()]['area'] = $area;
            }

        }
        usort(
            $encounterMapsUse,
            function (RegionMap $a, RegionMap $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );
        foreach ($encounterMaps as &$encounterMapSet) {
            usort(
                $encounterMapSet,
                function (LocationMap $a, LocationMap $b) {
                    return $b->getZIndex() - $a->getZIndex();
                }
            );
        }
        unset($encounterMapSet);

        // Held items count
        $heldItemsCount = $this->wildHeldItemRepo->countByPokemon($pokemon, $version);

        // Stat Percentiles
        $statPercentiles = $this->calcStatPercentiles($pokemon, $version);

        // Experience map
        $expMap = [];
        foreach (range(1, 100) as $level) {
            $expMap[$level] = $this->levelUp->experienceRequired($level, $pokemon->getGrowthRate());
        }

        // Evolution tree
        $evoTree = $this->pokemonRepo->buildEvolutionTree($pokemon);
        $evoTreeData = $this->buildEvolutionData($evoTree, $version);

        return $this->render(
            'pokemon/view.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'pokemon_view',
                    [
                        'versionSlug' => '__VERSION__',
                        'speciesSlug' => $speciesSlug,
                        'pokemonSlug' => $pokemonSlug,
                    ]
                ),
                'species' => $species,
                'pokemon' => $pokemon,
                'form' => $form,
                'exp_map' => $expMap,
                'stat_percentiles' => $statPercentiles,
                'breeding_pokemon_table' => $canBreedWithTable,
                'hatch_steps_map' => $pokemon->getHatchSteps()
                    ? $this->breeding->hatchSteps($version, $pokemon->getHatchSteps())
                    : null,
                'held_items_table' => $heldItemsTable,
                'held_items_count' => $heldItemsCount,
                'evo_tree_data' => $evoTreeData,
                'move_learn_methods' => $moveLearnMethods,
                'move_tables' => $moveTables,
//                'encounter_table' => $encounterTable,
                'encounters' => $encounters,
                'encounters_by_location' => $encountersByLocation,
                'encounter_maps' => $encounterMaps,
                'encounter_maps_use' => $encounterMapsUse,
                'encounters_not_highlighted' => $encountersNotHighlighted,
            ]
        );
    }

    /**
     * @param LocationInVersionGroup $location
     *
     * @return LocationMap|null
     */
    private function findLocationMap(LocationInVersionGroup $location): ?LocationMap
    {
        if ($location->getMap() !== null) {
            return $location->getMap();
        }

        if ($location->getSuperLocation() !== null) {
            return $this->findLocationMap($location->getSuperLocation());
        }

        return null;
    }

    /**
     * @param Pokemon $pokemon
     * @param Version $version
     *
     * @return int[]
     */
    protected function calcStatPercentiles(Pokemon $pokemon, Version $version): array
    {
        $percentiles = [];
        foreach ($pokemon->getStats() as $pokemonStat) {
            $percentile = $this->pokemonStatRepo->calcPercentileForStat($version, $pokemonStat);
            $percentiles[$pokemonStat->getStat()->getSlug()] = $percentile;
        }
        $percentiles['total'] = $this->pokemonStatRepo->calcPercentileForStat($version, $pokemon->getStatTotal());

        return $percentiles;
    }

    /**
     * @param array $evoTree
     * @param Version $version
     *
     * @return array
     */
    protected function buildEvolutionData(array $evoTree, Version $version): array
    {
        /** @var Pokemon $pokemon */
        $pokemon = $evoTree['entity'];
        /** @var bool $active */
        $active = $evoTree['active'];
        /** @var array[] $children */
        $children = $evoTree['children'] ?? [];
        $triggers = $this->getEvoTriggers($pokemon);

        foreach ($evoTree['children'] as &$child) {
            $child = $this->buildEvolutionData($child, $version);
        }
        unset($child);

        $evoTree['html'] = $this->renderView(
            'pokemon/evo_panel.html.twig',
            [
                'version' => $version,
                'pokemon' => $pokemon,
                'active' => $active,
                'children' => $children,
                'triggers' => $triggers,
            ]
        );
        unset($evoTree['entity']);

        return $evoTree;
    }

    /**
     * @param Pokemon $pokemon
     *
     * @return EvolutionTrigger[]
     */
    protected function getEvoTriggers(Pokemon $pokemon): array
    {
        $triggers = [];
        foreach ($pokemon->getEvolutionConditions() as $evolutionCondition) {
            $triggers[] = $evolutionCondition->getEvolutionTrigger();
        }

        $triggers = array_unique($triggers);

        return $triggers;
    }
}
