<?php

namespace App\Controller;

use App\DataTable\Type\LocationEncounterPokemonTableType;
use App\Entity\LocationArea;
use App\Entity\LocationInVersionGroup;
use App\Entity\Version;
use App\Repository\LocationInVersionGroupRepository;
use App\Repository\LocationMapRepository;
use App\Repository\RegionInVersionGroupRepository;
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
     * @var RegionInVersionGroupRepository
     */
    private $regionRepo;

    /**
     * @var LocationMapRepository
     */
    private $locationMapRepo;

    /**
     * LocationController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param LocationInVersionGroupRepository $locationRepo
     * @param RegionInVersionGroupRepository $regionRepo
     * @param LocationMapRepository $locationMapRepo
     */
    public function __construct(
        DataTableFactory $dataTableFactory,
        LocationInVersionGroupRepository $locationRepo,
        RegionInVersionGroupRepository $regionRepo,
        LocationMapRepository $locationMapRepo
    ) {
        parent::__construct($dataTableFactory);

        $this->locationRepo = $locationRepo;
        $this->regionRepo = $regionRepo;
        $this->locationMapRepo = $locationMapRepo;
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
        $locationMaps = [];
        foreach ($regions as $region) {
            $locations[$region->getSlug()] = $this->locationRepo->findByRegion($region);
            usort(
                $locations[$region->getSlug()],
                function (LocationInVersionGroup $a, LocationInVersionGroup $b) {
                    return strnatcmp($a->getName(), $b->getName());
                }
            );
            foreach ($region->getMaps() as $map) {
                $locationMaps[$map->getSlug()] = $this->locationMapRepo->findByMap($map);
            }
        }

        return $this->render(
            'location/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('location_index', ['versionSlug' => '__VERSION__']),
                'regions' => $regions,
                'locations' => $locations,
                'location_maps' => $locationMaps,
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

        $encounterTables = $this->getEncounterTables($version, $request, $location->getAreas());
        // This may be a callback to get table data, in which case the table handled the request
        // already.
        if ($encounterTables instanceof Response) {
            return $encounterTables;
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

    /**
     * @param Version $version
     * @param Request $request
     * @param LocationArea[] $areas
     * @param LocationEncounterPokemonTableType[] $encounterTables
     *
     * @return Response|LocationEncounterPokemonTableType[]
     */
    private function getEncounterTables(Version $version, Request $request, $areas, array &$encounterTables = [])
    {
        foreach ($areas as $area) {
            $encounterTable = $this->dataTableFactory->createFromType(
                LocationEncounterPokemonTableType::class,
                [
                    'version' => $version,
                    'location_area' => $area,
                ]
            )->handleRequest($request);
            if ($encounterTable->isCallback()) {
                return $encounterTable->getResponse();
            }
            $encounterTables[$area->getTreePath()] = $encounterTable;

            // Recursively add children
            if ($area->getTreeChildren()) {
                $ret = $this->getEncounterTables($version, $request, $area->getTreeChildren(), $encounterTables);
                if ($ret instanceof Response) {
                    // If a table has handled the request, break out and return the response.
                    return $ret;
                }
            }
        }

        return $encounterTables;
    }
}
