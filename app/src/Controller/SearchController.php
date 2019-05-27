<?php

namespace App\Controller;

use App\Entity\Version;
use App\Form\SiteSearchType;
use FOS\ElasticaBundle\Finder\FinderInterface;
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
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FinderInterface
     */
    private $finder;

    /**
     * SearchController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param FinderInterface $finder
     */
    public function __construct(FormFactoryInterface $formFactory, FinderInterface $finder)
    {
        $this->formFactory = $formFactory;
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
        // Need to handle both the form in the navbar and the form on the results page.
        $navbarForm = $this->formFactory->create(SiteSearchType::class);
        $navbarForm->handleRequest($request);
        $resultsForm = $this->formFactory->createNamed($navbarForm->getName().'_results', SiteSearchType::class);
        $resultsForm->handleRequest($request);
        if ($navbarForm->isSubmitted()) {
            $submittedForm = $navbarForm;
        } else {
            $submittedForm = $resultsForm;
        }

        if ($submittedForm->isSubmitted() && $submittedForm->isValid()) {
            $searchQ = $submittedForm->getData();
            $q = $searchQ['q'];
            $results = $this->finder->find($q);
        }

        return $this->render(
            'search/search.html.twig',
            [
                'version' => $version,
                'uri_template' => $this->generateUrl('search_search', ['versionSlug' => '__VERSION__']),
                'form' => $resultsForm->createView(),
                'query' => $q ?? null,
                'results' => $results ?? [],
            ]
        );
    }
}
