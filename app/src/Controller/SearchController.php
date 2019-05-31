<?php

namespace App\Controller;

use App\Elastica\ElasticaToModelTransformer;
use App\Entity\Version;
use App\Form\SiteSearchType;
use App\Repository\VersionRepository;
use Elastica\Client;
use Elastica\Query;
use Elastica\Search;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SearchController
 *
 * @Route("/dex/{versionSlug}/search", name="search_")
 */
class SearchController extends AbstractController
{
    public const ALL_VERSIONS_SLUG = 'any';

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Client
     */
    private $elasticaClient;

    /**
     * @var ElasticaToModelTransformer
     */
    private $elasticaToModelTransformer;

    /**
     * @var VersionRepository
     */
    private $versionRepo;

    /**
     * SearchController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param Client $elasticClient
     * @param ElasticaToModelTransformer $elasticaToModelTransformer
     * @param VersionRepository $versionRepo
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        Client $elasticClient,
        ElasticaToModelTransformer $elasticaToModelTransformer,
        VersionRepository $versionRepo
    ) {
        $this->formFactory = $formFactory;
        $this->elasticaClient = $elasticClient;
        $this->elasticaToModelTransformer = $elasticaToModelTransformer;
        $this->versionRepo = $versionRepo;
    }

    /**
     * @param Request $request
     * @param Version $version
     *
     * @return Response
     *
     * @Route("/", name="search")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function search(Request $request, Version $version): Response
    {
        $searchDefaults = [
            'all_versions' => SiteSearchType::CHOICE_ALL_VERSIONS,
        ];
        $form = $this->formFactory->create(SiteSearchType::class, $searchDefaults, ['version' => $version]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchQ = $form->getData();
            $q = $searchQ['q'];
            $query = $this->buildSearchQuery($q, $version, $searchQ['all_versions']);
            $search = new Search($this->elasticaClient);
            $search->setQuery($query);
            $search->setOption('search_type', 'dfs_query_then_fetch');
            $elasticaResults = $search->search();
            $resultEntities = $this->elasticaToModelTransformer->transform($elasticaResults->getResults());
            $results = [];
            foreach ($elasticaResults as $k => $elasticaResult) {
                $results[] = [
                    'elastica' => $elasticaResult,
                    'entity' => $resultEntities[$k],
                ];
            }
        }

        $uriTemplate = $this->generateUrl(
            'search_search',
            array_merge(['versionSlug' => '__VERSION__'], $request->query->all())
        );
        $params = [
            'uri_template' => $uriTemplate,
            'form' => $form->createView(),
            'query' => $q ?? null,
            'results' => $results ?? [],
        ];
        if ($version) {
            $params['version'] = $version;
        }

        return $this->render('search/search.html.twig', $params);
    }

    /**
     * Integrate the current version into the search query.
     *
     * @param string $q
     * @param Version|null $version
     * @param bool $fromAllVersions
     *   Include results from all versions
     *
     * @return Query\AbstractQuery
     */
    private function buildSearchQuery(string $q, ?Version $version, bool $fromAllVersions = false): Query\AbstractQuery
    {
        $query = new Query\BoolQuery();

        $nameQuery = new Query\QueryString();
        $nameQuery->setQuery($q);
        $nameQuery->setBoost(20);
        $query->addShould($nameQuery);
        $bodyQuery = new Query\QueryString($q);
        $bodyQuery->setBoost(0.5);
        $query->addShould($bodyQuery);
        if ($version) {
            if ($fromAllVersions) {
                // Allow results from all versions, but prioritize those from the current version
                $query->addShould($this->getSearchQueryVersionConstraints($version, 2));
            } else {
                // Only allow results from the current version
                $query->addFilter($this->getSearchQueryVersionConstraints($version));
            }
        }

        return $query;
    }

    private function getSearchQueryVersionConstraints(Version $version, float $boost = 1.0): Query\AbstractQuery
    {
        $versionConstraints = [
            'version.id' => $version->getId(),
            'version_group.id' => $version->getVersionGroup()->getId(),
            'generation.id' => $version->getVersionGroup()->getGeneration()->getId(),
        ];

        $orQuery = new Query\BoolQuery();
        $withVersionsQuery = new Query\BoolQuery();
        $withoutVersionsQuery = new Query\BoolQuery();
        foreach ($versionConstraints as $key => $value) {
            $termQuery = new Query\Term();
            $termQuery->setTerm($key, $value);
            $withVersionsQuery->addShould($termQuery);

            // Allow things with no version info
            $withoutVersionsQuery->addMustNot(new Query\Exists($key));
        }
        $orQuery->addShould($withVersionsQuery);
        $orQuery->addShould($withoutVersionsQuery);
        $orQuery->setBoost($boost);

        return $orQuery;
    }
}
