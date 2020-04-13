<?php

namespace App\Controller;

use App\DataTable\Type\FlagMoveTableType;
use App\DataTable\Type\MovePokemonTableType;
use App\DataTable\Type\MoveTableType;
use App\DataTable\Type\SimilarMoveTableType;
use App\Entity\MoveFlag;
use App\Entity\MoveLearnMethod;
use App\Entity\Version;
use App\Repository\MoveEffectInVersionGroupRepository;
use App\Repository\MoveInVersionGroupRepository;
use App\Repository\MoveLearnMethodRepository;
use App\Repository\PokemonMoveRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MoveController
 *
 * @Route("/dex/{versionSlug}/move", name="move_")
 */
class MoveController extends AbstractDexController
{
    /**
     * @var MoveInVersionGroupRepository
     */
    private $moveRepo;

    /**
     * @var PokemonMoveRepository
     */
    private $pokemonMoveRepo;

    /**
     * @var MoveLearnMethodRepository
     */
    private $moveLearnMethodRepo;

    /**
     * MoveController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param MoveInVersionGroupRepository $moveRepo
     * @param PokemonMoveRepository $pokemonMoveRepo
     * @param MoveLearnMethodRepository $moveLearnMethodRepo
     */
    public function __construct(
        DataTableFactory $dataTableFactory,
        MoveInVersionGroupRepository $moveRepo,
        PokemonMoveRepository $pokemonMoveRepo,
        MoveLearnMethodRepository $moveLearnMethodRepo
    ) {
        parent::__construct($dataTableFactory);

        $this->moveRepo = $moveRepo;
        $this->pokemonMoveRepo = $pokemonMoveRepo;
        $this->moveLearnMethodRepo = $moveLearnMethodRepo;
    }

    /**
     * Present item data for debugging
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Version $version
     * @param string $appEnv
     * @param \App\Repository\MoveEffectInVersionGroupRepository $effectRepo
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/debug_view/effect", name="debug")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function debugEffects(
        Request $request,
        Version $version,
        string $appEnv,
        MoveEffectInVersionGroupRepository $effectRepo
    ): Response {
        if ($appEnv == 'prod') {
            throw new NotFoundHttpException();
        }
        // This can use a large amount of memory, but for debug purposes this is ok.
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');

        $qb = $effectRepo->createQueryBuilder('mevg');
        $qb->join('mevg.parent', 'me')
            ->where('mevg.versionGroup = :versionGroup')
            ->orderBy('me.id')
            ->setParameter('versionGroup', $version->getVersionGroup());
        $q = $qb->getQuery();
        /** @var \App\Entity\MoveEffectInVersionGroup[] $effects */
        $effects = $q->execute();
        /** @var \App\Entity\MoveInVersionGroup[] $inMoves */
        $inMoves = [];
        foreach ($effects as $effect) {
            $inMoves[$effect->getId()] = $this->moveRepo->findBy(['effect' => $effect], ['name' => 'ASC']);
        }

        return $this->render(
            'move/debug_effect.html.twig',
            [
                'version' => $version,
                'effects' => $effects,
                'in_moves' => $inMoves,
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
        $moveTable = $this->dataTableFactory->createFromType(
            MoveTableType::class,
            [
                'version' => $version,
            ]
        )->handleRequest($request);
        if ($moveTable->isCallback()) {
            return $moveTable->getResponse();
        }

        return $this->render(
            'move/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('move_index', ['versionSlug' => '__VERSION__']),
                'move_table' => $moveTable,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $moveSlug
     *
     * @return Response
     *
     * @Route("/{moveSlug}", name="view")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function view(Request $request, Version $version, string $moveSlug): Response
    {
        $move = $this->moveRepo->findOneByVersion($moveSlug, $version);
        if ($move === null) {
            throw new NotFoundHttpException();
        }

        // Similar moves
        $similarMoveTable = $this->dataTableFactory->createFromType(
            SimilarMoveTableType::class,
            [
                'version' => $version,
                'move' => $move,
            ]
        )->handleRequest($request);
        if ($similarMoveTable->isCallback()) {
            return $similarMoveTable->getResponse();
        }

        // Pokemon
        $moveLearnMethods = $this->moveLearnMethodRepo->findBy([], ['position' => 'asc']);
        /** @var MoveLearnMethod[] $usedLearnMethods */
        $usedLearnMethods = [];
        $pokemonTables = [];
        foreach ($moveLearnMethods as $moveLearnMethod) {
            if ($this->pokemonMoveRepo->countByMoveAndLearnMethod($move, $moveLearnMethod) > 0) {
                $usedLearnMethods[] = $moveLearnMethod;
                $pokemonTable = $this->dataTableFactory->createFromType(
                    MovePokemonTableType::class,
                    [
                        'version' => $version,
                        'move' => $move,
                        'learnMethod' => $moveLearnMethod,
                    ]
                )->handleRequest($request);
                if ($pokemonTable->isCallback()) {
                    return $pokemonTable->getResponse();
                }

                $pokemonTables[$moveLearnMethod->getSlug()] = $pokemonTable;
            }
        }

        return $this->render(
            'move/view.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'move_view',
                    [
                        'versionSlug' => '__VERSION__',
                        'moveSlug' => $moveSlug,
                    ]
                ),
                'move' => $move,
                'similar_move_table' => $similarMoveTable,
                'move_learn_methods' => $usedLearnMethods,
                'pokemon_tables' => $pokemonTables,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param MoveFlag $flag
     * @return Response
     *
     * @Route("/flagMoves/{flag}", name="flag_moves")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     * @ParamConverter("flag", options={"mapping": {"flag": "slug"}})
     */
    public function flagMoves(Request $request, Version $version, MoveFlag $flag): ?Response
    {
        $moveTable = $this->dataTableFactory->createFromType(
            FlagMoveTableType::class,
            [
                'version' => $version,
                'flag' => $flag,
            ]
        )->handleRequest($request);
        if ($moveTable->isCallback()) {
            return $moveTable->getResponse();
        }

        return $this->render(
            'move/flag_moves.html.twig',
            [
                'move_table' => $moveTable,
                'callback_url' => $this->generateUrl(
                    'move_flag_moves',
                    [
                        'versionSlug' => $version->getSlug(),
                        'flag' => $flag->getSlug(),
                    ]
                ),
            ]
        );
    }
}
