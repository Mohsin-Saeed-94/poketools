<?php

namespace App\Controller;

use App\DataTable\Type\BreedingPokemonTableType;
use App\DataTable\Type\MoveTableType;
use App\DataTable\Type\PokemonHeldItemTableType;
use App\DataTable\Type\PokemonMoveTableType;
use App\DataTable\Type\PokemonTableType;
use App\Entity\EvolutionTrigger;
use App\Entity\Pokemon;
use App\Entity\Version;
use App\Mechanic\Breeding;
use App\Mechanic\LevelUp;
use App\Repository\MoveLearnMethodRepository;
use App\Repository\PokemonFormRepository;
use App\Repository\PokemonMoveRepository;
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
            ]
        );
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
