<?php

namespace App\Form;

use App\Controller\SearchController;
use App\Entity\Version;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
    public const CHOICE_ALL_VERSIONS = 1;
    public const CHOICE_ACTIVE_VERSION = 0;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * SearchFormType constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Version|null $version */
        $version = $options['version'];

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
        );


        $choices = [
            'All versions' => self::CHOICE_ALL_VERSIONS,
        ];
        if ($version) {
            $choices[$version->getName().' version only'] = self::CHOICE_ACTIVE_VERSION;
            $default = self::CHOICE_ACTIVE_VERSION;
        } else {
            $choices['Active version only'] = self::CHOICE_ACTIVE_VERSION;
            $default = self::CHOICE_ALL_VERSIONS;
        }
        $builder->add(
            'all_versions',
            ChoiceType::class,
            [
                'label' => false,
                'choices' => $choices,
                'multiple' => false,
                'expanded' => true,
                'data' => $default,
                'empty_data' => $default,
                'disabled' => $version === null,
            ]
        );

        $builder->add(
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
                    'versionSlug' => $version ? $version->getSlug() : SearchController::ALL_VERSIONS_SLUG,
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
                'version' => null,
            ]
        );
        $resolver->setAllowedTypes('version', ['null', Version::class]);
    }
}
