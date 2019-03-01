<?php

namespace App\Controller;

use App\Entity\Version;
use App\Repository\TypeChartRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * TypeController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param TypeChartRepository $typeChartRepo
     */
    public function __construct(DataTableFactory $dataTableFactory, TypeChartRepository $typeChartRepo)
    {
        parent::__construct($dataTableFactory);

        $this->typeChartRepo = $typeChartRepo;
    }

    /**
     * @Route("/", name="index")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function index(Request $request, Version $version): Response
    {
        $typeChart = $this->typeChartRepo->findOneByVersion($version);
        $types = $typeChart->getTypes();

        return $this->render(
            'type/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('type_index', ['versionSlug' => '__VERSION__']),
                'type_chart' => $typeChart,
                'types' => $types,
            ]
        );
    }
}
