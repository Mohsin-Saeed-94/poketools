<?php

namespace App\Controller;

use App\Elastica\ElasticaToModelTransformer;
use App\Entity\Version;
use App\Form\SiteSearchType;
use App\Repository\VersionRepository;
use Elastica\Client;
use Elastica\Query;
use Elastica\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SearchController
 *
 * @Route("/dex/{versionSlug}/search", name="search_", defaults={"versionSlug": "any"})
 */
class SearchController extends AbstractController
{
    public const ALL_VERSIONS_SLUG = 'any';
    private const BOOST_VERSION = 10.0;

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
     * @param string $versionSlug
     *
     * @return Response
     *
     * @Route("/", name="search")
     */
    public function search(Request $request, string $versionSlug): Response
    {
        $version = $this->getVersion($versionSlug);
        $form = $this->formFactory->create(SiteSearchType::class, ['all_versions' => SiteSearchType::CHOICE_ALL_VERSIONS], ['version' => $version]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchQ = $form->getData();
            $q = $searchQ['q'];
            $query = $this->buildSearchQuery($q, $version, $searchQ['all_versions']);
            $search = new Search($this->elasticaClient);
            $search->setQuery($query);
            $results = $search->search();
            $results = $this->elasticaToModelTransformer->transform($results->getResults());
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
     * @param string $versionSlug
     *
     * @return Version|null
     */
    private function getVersion(string $versionSlug): ?Version
    {
        if ($versionSlug !== self::ALL_VERSIONS_SLUG) {
            $version = $this->versionRepo->findOneBy(['slug' => $versionSlug]);
            if ($version === null) {
                throw new NotFoundHttpException();
            }
        } else {
            $version = null;
        }

        return $version;
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

        // Using query string allows some primitive advanced searches
        // See https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#query-string-syntax
        $queryString = new Query\QueryString($q);
        $query->addMust($queryString);
        if ($version) {
            $versions = [
                'version.id' => $version->getId(),
                'version_group.id' => $version->getVersionGroup()->getId(),
                'generation.id' => $version->getVersionGroup()->getGeneration()->getId(),
            ];
            if ($fromAllVersions) {
                foreach ($versions as $key => $value) {
                    $termQuery = new Query\Term();
                    $termQuery->setTerm($key, $value, self::BOOST_VERSION);
                    $query->addShould($termQuery);
                }
            } else {
                $orQuery = new Query\BoolQuery();
                foreach ($versions as $key => $value) {
                    $termQuery = new Query\Term();
                    $termQuery->setTerm($key, $value);
                    $orQuery->addShould($termQuery);
                }
                $query->addFilter($orQuery);
            }
        }

        return $query;
    }
}
