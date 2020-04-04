<?php

namespace App\Controller;

use App\DataTable\Type\ContestTypeMoveTableType;
use App\DataTable\Type\TypeMoveTableType;
use App\DataTable\Type\TypePokemonTableType;
use App\Entity\ContestType;
use App\Entity\Version;
use App\Repository\ContestTypeRepository;
use App\Repository\TypeChartRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TypeController
 *
 * @Route("/dex/{versionSlug}/type", name="type_")
 */
class TypeController extends AbstractDexController
{
    /**
     * @var TypeChartRepository
     */
    private $typeChartRepo;

    private $contestTypeRepo;

    /**
     * TypeController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param TypeChartRepository $typeChartRepo
     * @param ContestTypeRepository $contestTypeRepo
     */
    public function __construct(
        DataTableFactory $dataTableFactory,
        TypeChartRepository $typeChartRepo,
        ContestTypeRepository $contestTypeRepo
    ) {
        parent::__construct($dataTableFactory);

        $this->typeChartRepo = $typeChartRepo;
        $this->contestTypeRepo = $contestTypeRepo;
    }

    /**
     * param Request $request
     *
     * @param Version $version
     *
     * @return Response
     *
     * @Route("/", name="index")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function index(Version $version): Response
    {
        $typeChart = $this->typeChartRepo->findOneByVersion($version);
        $types = $typeChart->getTypes();
        if ($version->getVersionGroup()->hasFeatureString('contests')
            || $version->getVersionGroup()->hasFeatureString('super-contests')) {
            $contestTypes = $this->contestTypeRepo->findAll();
        } else {
            $contestTypes = [];
        }

        return $this->render(
            'type/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('type_index', ['versionSlug' => '__VERSION__']),
                'type_chart' => $typeChart,
                'types' => $types,
                'contest_types' => $contestTypes,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $typeSlug
     *
     * @return Response
     *
     * @Route("/{typeSlug}", name="view")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function view(Request $request, Version $version, string $typeSlug): Response
    {
        $type = $this->typeChartRepo->findTypeInTypeChart($typeSlug, $version);
        if ($type === null) {
            if ($version->getVersionGroup()->hasFeatureString('contests')
                || $version->getVersionGroup()->hasFeatureString('super-contests')) {
                // Is it a contest type?
                $type = $this->contestTypeRepo->findOneBy(['slug' => $typeSlug]);
                if ($type !== null) {
                    return $this->viewContestType($request, $version, $type);
                }
            }
            throw new NotFoundHttpException();
        }

        $pokemonTable = $this->dataTableFactory->createFromType(
            TypePokemonTableType::class,
            [
                'version' => $version,
                'type' => $type,
            ]
        )->handleRequest($request);
        if ($pokemonTable->isCallback()) {
            return $pokemonTable->getResponse();
        }

        $moveTable = $this->dataTableFactory->createFromType(
            TypeMoveTableType::class,
            [
                'version' => $version,
                'type' => $type,
            ]
        )->handleRequest($request);
        if ($moveTable->isCallback()) {
            return $moveTable->getResponse();
        }

        return $this->render(
            'type/view.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'type_view',
                    ['versionSlug' => '__VERSION__', 'typeSlug' => $typeSlug]
                ),
                'type' => $type,
                'pokemon_table' => $pokemonTable,
                'move_table' => $moveTable,
            ]
        );
    }

    /**
     * Requests to the route `type_view` are forwarded here if the slug is not a type
     * but does match a contest type.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Version $version
     * @param \App\Entity\ContestType $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function viewContestType(Request $request, Version $version, ContestType $type): Response
    {
        $moveTable = $this->dataTableFactory->createFromType(
            ContestTypeMoveTableType::class,
            [
                'version' => $version,
                'type' => $type,
            ]
        )->handleRequest($request);
        if ($moveTable->isCallback()) {
            return $moveTable->getResponse();
        }

        return $this->render(
            'type/view_contest_type.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'type_view',
                    ['versionSlug' => '__VERSION__', 'typeSlug' => $type->getSlug()]
                ),
                'type' => $type,
                'move_table' => $moveTable,
            ]
        );
    }
}
