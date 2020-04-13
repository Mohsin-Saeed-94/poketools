<?php

namespace App\Controller;

use App\DataTable\Type\EvolvesWithItemPokemonTableType;
use App\DataTable\Type\ItemTableType;
use App\DataTable\Type\MovePokemonTableType;
use App\Entity\MoveLearnMethod;
use App\Entity\Version;
use App\Repository\ItemInVersionGroupRepository;
use App\Repository\ItemPocketInVersionGroupRepository;
use App\Repository\MoveLearnMethodRepository;
use App\Repository\PokemonWildHeldItemRepository;
use App\Repository\ShopItemRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ItemController
 *
 * @Route("/dex/{versionSlug}/item", name="item_")
 */
class ItemController extends AbstractDexController
{
    /**
     * @var ItemInVersionGroupRepository
     */
    private $itemRepo;

    /**
     * @var ItemPocketInVersionGroupRepository
     */
    private $pocketRepo;

    /**
     * @var PokemonWildHeldItemRepository
     */
    private $heldItemRepo;

    /**
     * @var MoveLearnMethod
     */
    private $machineLearnMethod;
    /**
     * @var ShopItemRepository
     */
    private $shopItemRepo;

    /**
     * ItemController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param ItemInVersionGroupRepository $itemRepo
     * @param ItemPocketInVersionGroupRepository $pocketRepo
     * @param PokemonWildHeldItemRepository $heldItemRepo
     * @param MoveLearnMethodRepository $moveLearnMethodRepo
     * @param ShopItemRepository $shopItemRepo
     */
    public function __construct(
        DataTableFactory $dataTableFactory,
        ItemInVersionGroupRepository $itemRepo,
        ItemPocketInVersionGroupRepository $pocketRepo,
        PokemonWildHeldItemRepository $heldItemRepo,
        MoveLearnMethodRepository $moveLearnMethodRepo,
        ShopItemRepository $shopItemRepo
    ) {
        parent::__construct($dataTableFactory);

        $this->itemRepo = $itemRepo;
        $this->pocketRepo = $pocketRepo;
        $this->heldItemRepo = $heldItemRepo;
        $this->machineLearnMethod = $moveLearnMethodRepo->findOneBy(['slug' => 'machine']);
        $this->shopItemRepo = $shopItemRepo;
    }

    /**
     * Present item data for debugging
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Version $version
     * @param string $appEnv
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/debug_view", name="debug")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function debug(Request $request, Version $version, string $appEnv): Response
    {
        if ($appEnv == 'prod') {
            throw new NotFoundHttpException();
        }
        // This can use a large amount of memory, but for debug purposes this is ok.
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $items = $this->itemRepo->findBy(['versionGroup' => $version->getVersionGroup()], ['name' => 'ASC']);

        return $this->render(
            'item/debug.html.twig',
            [
                'version' => $version,
                'items' => $items,
            ]
        );
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
    public function index(Request $request, Version $version): Response
    {
        $pockets = $this->pocketRepo->findByVersion($version);

        $itemTables = [];
        foreach ($pockets as $pocket) {
            $itemTable = $this->dataTableFactory->createFromType(
                ItemTableType::class,
                [
                    'version' => $version,
                    'pocket' => $pocket,
                ]
            )->handleRequest($request);
            if ($itemTable->isCallback()) {
                return $itemTable->getResponse();
            }
            $itemTables[$pocket->getSlug()] = $itemTable;
        }

        return $this->render(
            'item/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('item_index', ['versionSlug' => '__VERSION__']),
                'pockets' => $pockets,
                'item_tables' => $itemTables,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $itemSlug
     *
     * @return Response
     *
     * @Route("/{itemSlug}", name="view")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function view(Request $request, Version $version, string $itemSlug): Response
    {
        $item = $this->itemRepo->findOneByVersion($itemSlug, $version);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        $wildHeldItems = $this->heldItemRepo->findByItemAndVersion($item, $version);
        $shopItems = $this->shopItemRepo->findByItem($item);

        return $this->render(
            'item/view.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'item_view',
                    ['versionSlug' => '__VERSION__', 'itemSlug' => $itemSlug]
                ),
                'item' => $item,
                'wild_held_items' => $wildHeldItems,
                'shop_items' => $shopItems,
            ]
        );
    }


    /**
     * @param Request $request
     * @param Version $version
     * @param string $itemSlug
     *
     * @return Response
     *
     * @Route("/machinePokemon/{itemSlug}", name="machine_pokemon")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function tmPokemon(Request $request, Version $version, string $itemSlug): ?Response
    {
        $item = $this->itemRepo->findOneByVersion($itemSlug, $version);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        $pokemonTable = $this->dataTableFactory->createFromType(
            MovePokemonTableType::class,
            [
                'version' => $version,
                'move' => $item->getMachine()->getMove(),
                'learnMethod' => $this->machineLearnMethod,
            ]
        )->handleRequest($request);
        if ($pokemonTable->isCallback()) {
            return $pokemonTable->getResponse();
        }

        return $this->render(
            'item/machine_pokemon.html.twig',
            [
                'pokemon_table' => $pokemonTable,
                'callback_url' => $this->generateUrl(
                    'item_machine_pokemon',
                    [
                        'versionSlug' => $version->getSlug(),
                        'itemSlug' => $itemSlug,
                    ]
                ),
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $itemSlug
     *
     * @return Response
     *
     * @Route("/evolutionItemPokemon/{itemSlug}", name="evolution_pokemon")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function evolutionPokemon(Request $request, Version $version, string $itemSlug): ?Response
    {
        $item = $this->itemRepo->findOneByVersion($itemSlug, $version);
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        $pokemonTable = $this->dataTableFactory->createFromType(
            EvolvesWithItemPokemonTableType::class,
            [
                'version' => $version,
                'item' => $item,
            ]
        )->handleRequest($request);
        if ($pokemonTable->isCallback()) {
            return $pokemonTable->getResponse();
        }

        return $this->render(
            'item/evolution_pokemon.html.twig',
            [
                'pokemon_table' => $pokemonTable,
                'callback_url' => $this->generateUrl(
                    'item_evolution_pokemon',
                    [
                        'versionSlug' => $version->getSlug(),
                        'itemSlug' => $itemSlug,
                    ]
                ),
            ]
        );
    }
}
