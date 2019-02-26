<?php

namespace App\Controller;

use App\DataTable\Type\NaturePokemonTableType;
use App\DataTable\Type\NatureTableType;
use App\Entity\Version;
use App\Repository\CharacteristicRepository;
use App\Repository\NatureRepository;
use Doctrine\ORM\NonUniqueResultException;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NatureController
 *
 * @Route("/dex/{versionSlug}/nature", name="nature_")
 */
class NatureController extends AbstractDexController
{
    /**
     * @var NatureRepository
     */
    private $natureRepo;

    /**
     * @var CharacteristicRepository
     */
    private $characteristicRepo;

    /**
     * NatureController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param NatureRepository $natureRepo
     */
    public function __construct(
        DataTableFactory $dataTableFactory,
        NatureRepository $natureRepo,
        CharacteristicRepository $characteristicRepo
    ) {
        parent::__construct($dataTableFactory);

        $this->natureRepo = $natureRepo;
        $this->characteristicRepo = $characteristicRepo;
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
        // Natures list
        $table = $this->dataTableFactory->createFromType(
            NatureTableType::class,
            ['version' => $version]
        )->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        // Characteristics table
        $characteristics = $this->characteristicRepo->findAll();
        $ivDeterminators = $this->characteristicRepo->findAllIvDeterminators();
        $stats = [];
        foreach ($characteristics as $characteristic) {
            $stats[] = $characteristic->getStat();
        }
        $stats = array_unique($stats);

        return $this->render(
            'nature/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('nature_index', ['versionSlug' => '__VERSION__']),
                'nature_table' => $table,
                'characteristics' => $characteristics,
                'iv_determinators' => $ivDeterminators,
                'stats' => $stats,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $natureSlug
     *
     * @return Response
     *
     * @Route("/{natureSlug}", name="view")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function view(Request $request, Version $version, string $natureSlug): Response
    {
        try {
            $nature = $this->natureRepo->findOneBy(['slug' => $natureSlug]);
        } catch (NonUniqueResultException $e) {
            throw new NotFoundHttpException();
        }
        if (!$nature) {
            throw new NotFoundHttpException();
        }

        // Pokemon Table
        $pokemonTable = $this->dataTableFactory->createFromType(
            NaturePokemonTableType::class,
            [
                'version' => $version,
                'nature' => $nature,
            ]
        )->handleRequest($request);
        if ($pokemonTable->isCallback()) {
            return $pokemonTable->getResponse();
        }

        return $this->render(
            'nature/view.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'nature_view',
                    [
                        'versionSlug' => '__VERSION__',
                        'natureSlug' => $natureSlug,
                    ]
                ),
                'nature' => $nature,
                'pokemon_table' => $pokemonTable,
            ]
        );
    }
}
