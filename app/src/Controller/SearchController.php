<?php

namespace App\Controller;

use App\Elastica\ElasticaToModelTransformer;
use App\Entity\AbilityInVersionGroup;
use App\Entity\ItemInVersionGroup;
use App\Entity\LocationInVersionGroup;
use App\Entity\MoveInVersionGroup;
use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\Type;
use App\Entity\Version;
use App\Entity\VersionGroup;
use App\Form\SiteSearchType;
use App\Repository\VersionRepository;
use App\Search\Finder;
use App\Search\Indexer;
use Elastica\Client;
use Elastica\Query;
use Elastica\Search;
use Elastica\Suggest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var VersionRepository
     */
    private $versionRepo;

    private Finder $finder;

    /**
     * SearchController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param VersionRepository $versionRepo
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VersionRepository $versionRepo,
        Finder $finder
    ) {
        $this->formFactory = $formFactory;
        $this->versionRepo = $versionRepo;
        $this->finder = $finder;
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
            $allVersions = (bool)$searchQ['all_versions'];
            $resultEntities = $this->finder->search($q, $allVersions ? null : $version);

            // If there is only one result, redirect there
            if (count($resultEntities) === 1) {
                return $this->redirect(
                    $this->getEntityUrl($resultEntities[array_key_first($resultEntities)], $version)
                );
            }

            $results = [];
            foreach ($resultEntities as $result) {
                $results[] = [
                    'entity' => $result,
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
     * Get the entity Url
     *
     * @param object $entity
     * @param Version $version
     *
     * @return string
     */
    private function getEntityUrl(object $entity, Version $version): string
    {
        // TODO: This pattern exists in several places.  Replace with a service.
        switch (get_class($entity)) {
            case Pokemon::class:
                /** @var Pokemon $entity */
                return $this->generateUrl(
                    'pokemon_view',
                    [
                        'versionSlug' => $this->useVersion($version, $entity->getSpecies()->getVersionGroup())->getSlug(
                        ),
                        'speciesSlug' => $entity->getSpecies()->getSlug(),
                        'pokemonSlug' => $entity->getSlug(),
                    ]
                );
            case MoveInVersionGroup::class:
                /** @var MoveInVersionGroup $entity */
                return $this->generateUrl(
                    'move_view',
                    [
                        'versionSlug' => $this->useVersion($version, $entity->getVersionGroup())->getSlug(),
                        'moveSlug' => $entity->getSlug(),
                    ]
                );
            case Type::class:
                /** @var Type $entity */
                return $this->generateUrl(
                    'type_view',
                    [
                        'versionSlug' => $version->getSlug(),
                        'typeSlug' => $entity->getSlug(),
                    ]
                );
            case ItemInVersionGroup::class:
                /** @var ItemInVersionGroup $entity */
                return $this->generateUrl(
                    'item_view',
                    [
                        'versionSlug' => $this->useVersion($version, $entity->getVersionGroup())->getSlug(),
                        'itemSlug' => $entity->getSlug(),
                    ]
                );
            case LocationInVersionGroup::class:
                /** @var LocationInVersionGroup $entity */
                return $this->generateUrl(
                    'location_view',
                    [
                        'versionSlug' => $this->useVersion($version, $entity->getVersionGroup())->getSlug(),
                        'locationSlug' => $entity->getSlug(),
                    ]
                );
            case Nature::class:
                /** @var Nature $entity */
                return $this->generateUrl(
                    'nature_view',
                    [
                        'versionSlug' => $version->getSlug(),
                        'natureSlug' => $entity->getSlug(),
                    ]
                );
            case AbilityInVersionGroup::class:
                /** @var AbilityInVersionGroup $entity */
                return $this->generateUrl(
                    'ability_view',
                    [
                        'versionSlug' => $this->useVersion($version, $entity->getVersionGroup())->getSlug(),
                        'abilitySlug' => $entity->getSlug(),
                    ]
                );
        }

        throw new \UnexpectedValueException(
            sprintf(
                'Cannot generate url for entity of type "%s".',
                get_class($entity)
            )
        );
    }

    /**
     * Resolve which version to use when creating the route
     *
     * @param Version $version
     * @param VersionGroup $entityVersionGroup
     *
     * @return Version
     */
    private function useVersion(Version $version, VersionGroup $entityVersionGroup): Version
    {
        if ($entityVersionGroup->getVersions()->contains($version)) {
            return $version;
        }

        return $entityVersionGroup->getVersions()->first();
    }

    /**
     * @param Request $request
     * @param Version $version
     *
     * @return Response
     *
     * @Route("/autocomplete.json", name="autocomplete")
     * @ParamConverter("version", options={"mapping": {"versionSlug": "slug"}})
     */
    public function autocomplete(Request $request, Version $version): Response
    {
        $q = $request->query->get('q');
        if (empty($q)) {
            return new JsonResponse([]);
        }

        $results = $this->finder->autocomplete($q, $version);

        $suggests = [];
        foreach ($results as $entity) {
            $suggests[] = [
                'value' => $entity->getName(),
                'html' => $this->renderSuggestion($entity),
            ];
        }

        return new JsonResponse($suggests);
    }

    /**
     * @param object $entity
     *
     * @return string
     */
    private function renderSuggestion(object $entity): string
    {
        $entityTemplates = [
            Pokemon::class => 'pokemon/suggestion.html.twig',
            ItemInVersionGroup::class => 'item/suggestion.html.twig',
            Type::class => 'type/suggestion.html.twig',
        ];

        $templateArgs = [
            'entity' => $entity,
        ];

        // Must allow that the actual entity class may be different because of proxy objects.
        foreach ($entityTemplates as $entityClass => $entityTemplate) {
            if (is_a($entity, $entityClass)) {
                return $this->renderView($entityTemplate, $templateArgs);
            }
        }

        return $this->renderView('search/base_suggestion.html.twig', $templateArgs);
    }
}
