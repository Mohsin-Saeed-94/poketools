<?php

namespace App\Form;

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
     * @var Version|null
     */
    private $activeVersion;

    /**
     * @var string
     */
    private $defaultVersionSlug;

    /**
     * SearchFormType constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param Version $activeVersion
     * @param string $defaultVersionSlug
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, ?Version $activeVersion, string $defaultVersionSlug)
    {
        $this->urlGenerator = $urlGenerator;
        $this->activeVersion = $activeVersion;
        $this->defaultVersionSlug = $defaultVersionSlug;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Version $version */
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
                    'versionSlug' => $this->useVersionSlug($version),
                ]
            )
        )
            ->setMethod('get');
    }

    /**
     * @param Version|null $version
     *
     * @return string
     */
    private function useVersionSlug(?Version $version): string
    {
        if ($version) {
            return $version->getSlug();
        }

        if ($this->activeVersion) {
            return $this->activeVersion->getSlug();
        }

        return $this->defaultVersionSlug;
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
