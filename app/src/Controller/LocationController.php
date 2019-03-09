<?php

namespace App\Controller;

use App\DataTable\Type\EncounterPokemonTableType;
use App\Entity\LocationInVersionGroup;
use App\Entity\Version;
use App\Repository\LocationInVersionGroupRepository;
use App\Repository\RegionRepository;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LocationController
 *
 * @Route("/dex/{versionSlug}/location", name="location_")
 */
class LocationController extends AbstractDexController
{
    /**
     * @var LocationInVersionGroupRepository
     */
    private $locationRepo;

    /**
     * @var RegionRepository
     */
    private $regionRepo;

    /**
     * LocationController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param LocationInVersionGroupRepository $locationRepo
     * @param RegionRepository $regionRepo
     */
    public function __construct(
        DataTableFactory $dataTableFactory,
        LocationInVersionGroupRepository $locationRepo,
        RegionRepository $regionRepo
    ) {
        parent::__construct($dataTableFactory);

        $this->locationRepo = $locationRepo;
        $this->regionRepo = $regionRepo;
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
        $regions = $this->regionRepo->findByVersion($version);
        $locations = [];
        foreach ($regions as $region) {
            $locations[$region->getSlug()] = $this->locationRepo->findByVersionAndRegion($version, $region);
            usort(
                $locations[$region->getSlug()],
                function (LocationInVersionGroup $a, LocationInVersionGroup $b) {
                    return strnatcmp($a->getName(), $b->getName());
                }
            );
        }

        return $this->render(
            'location/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('location_index', ['versionSlug' => '__VERSION__']),
                'regions' => $regions,
                'locations' => $locations,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $locationSlug
     *
     * @return Response
     *
     * @Route("/{locationSlug}", name="view")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function view(Request $request, Version $version, string $locationSlug): Response
    {
        $location = $this->locationRepo->findOneByVersion($locationSlug, $version);
        if ($location === null) {
            throw new NotFoundHttpException();
        }

        /** @var EncounterPokemonTableType[] $encounterTables */
        $encounterTables = [];
        foreach ($location->getAreas() as $area) {
            $encounterTable = $this->dataTableFactory->createFromType(
                EncounterPokemonTableType::class,
                [
                    'version' => $version,
                    'location_area' => $area,
                ]
            )->handleRequest($request);
            if ($encounterTable->isCallback()) {
                return $encounterTable->getResponse();
            }
            $encounterTables[$area->getSlug()] = $encounterTable;
        }

        return $this->render(
            'location/view.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'location_view',
                    [
                        'versionSlug' => '__VERSION__',
                        'locationSlug' => $locationSlug,
                    ]
                ),
                'location' => $location,
                'encounter_tables' => $encounterTables,
            ]
        );
    }
}
