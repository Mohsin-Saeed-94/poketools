<?php

namespace App\Form;

use App\Entity\Version;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Search form
 */
class SiteSearchType extends AbstractType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var Version|null
     */
    private $activeVersion;

    /**
     * SearchFormType constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param Version|null $activeVersion
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, ?Version $activeVersion)
    {
        $this->urlGenerator = $urlGenerator;
        $this->activeVersion = $activeVersion;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'q',
            SearchType::class,
            [
                'label' => 'Query',
                'required' => true,
                'attr' => [
                    'class' => 'pkt-form-search-query',
                ],
            ]
        )->add(
            'search',
            SubmitType::class,
            [
                'label' => 'Search',
                'attr' => [
                    'class' => 'pkt-form-search-submit',
                ],
            ]
        )->setAction(
            $this->urlGenerator->generate(
                'search_search',
                [
                    'versionSlug' => $this->activeVersion ? $this->activeVersion->getSlug() : null,
                ]
            )
        )
            ->setMethod('get');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'attr' => [
                    'class' => 'pkt-form-search',
                ],
                // Disable CSRF Protection for the search form
                'csrf_protection' => false,
            ]
        );
    }
}
