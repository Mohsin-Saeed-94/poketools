<?php

namespace App\Controller;

use App\DataTable\Type\AbilityPokemonTableType;
use App\DataTable\Type\AbilityTableType;
use App\Entity\Version;
use App\Repository\AbilityInVersionGroupRepository;
use Doctrine\ORM\NonUniqueResultException;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AbilityController
 *
 * @Route("/dex/{versionSlug}/ability", name="ability_")
 */
class AbilityController extends AbstractDexController
{
    /**
     * @var AbilityInVersionGroupRepository
     */
    private $abilityRepo;

    /**
     * AbilityController constructor.
     *
     * @param DataTableFactory $dataTableFactory
     * @param AbilityInVersionGroupRepository $abilityRepo
     */
    public function __construct(DataTableFactory $dataTableFactory, AbilityInVersionGroupRepository $abilityRepo)
    {
        parent::__construct($dataTableFactory);

        $this->abilityRepo = $abilityRepo;
    }

    /**
     * @param Request $request
     * @param Version $version
     *
     * @return Response
     *
     * @Route("/", name="index")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     *
     */
    public function index(Request $request, Version $version): Response
    {
        $table = $this->dataTableFactory->createFromType(
            AbilityTableType::class,
            ['version' => $version]
        )->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render(
            'ability/index.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('ability_index', ['versionSlug' => '__VERSION__']),
                'ability_table' => $table,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Version $version
     * @param string $abilitySlug
     *
     * @return Response
     *
     * @Route("/{abilitySlug}", name="view")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function view(Request $request, Version $version, string $abilitySlug): Response
    {
        try {
            $ability = $this->abilityRepo->findOneByVersion($abilitySlug, $version);
        } catch (NonUniqueResultException $e) {
            throw new NotFoundHttpException();
        }
        if (!$ability) {
            throw new NotFoundHttpException();
        }

        $pokemonTable = $this->dataTableFactory->createFromType(
            AbilityPokemonTableType::class,
            [
                'version' => $version,
                'ability' => $ability,
            ]
        )->handleRequest($request);
        if ($pokemonTable->isCallback()) {
            return $pokemonTable->getResponse();
        }

        return $this->render(
            'ability/view.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl(
                    'ability_view',
                    ['versionSlug' => '__VERSION__', 'abilitySlug' => $ability->getSlug()]
                ),
                'ability' => $ability,
                'pokemon_table' => $pokemonTable,
            ]
        );
    }
}
