<?php
/**
 * @file MenuBuilder.php
 */

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Main Menu Builder
 */
class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    private $urlGenerator;

    /**
     * MenuBuilder constructor.
     *
     * @param FactoryInterface $factory
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(FactoryInterface $factory, UrlGeneratorInterface $urlGenerator)
    {
        $this->factory = $factory;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Main Navbar menu
     *
     * @param array $options
     *
     * @return ItemInterface
     */
    public function navbarMenu(array $options): ItemInterface
    {
        // In all of these menus, set "data-uri-template" to replace "__VERSION__" with the version slug.
        $menu = $this->factory->createItem('root')
            ->setChildrenAttribute('class', 'navbar-nav');

        $pokemonUri = $this->urlGenerator->generate('pokemon_index', ['versionSlug' => '__VERSION__']);
        $menu->addChild(
            'Pokèmon',
            [
                'uri' => $pokemonUri,
                'linkAttributes' => ['data-uri-template' => $pokemonUri],
            ]
        );
        $movesUri = $this->urlGenerator->generate('move_index', ['versionSlug' => '__VERSION__']);
        $menu->addChild(
            'Moves',
            [
                'uri' => $movesUri,
                'linkAttributes' => ['data-uri-template' => $movesUri],
            ]
        );
        $typesUri = $this->urlGenerator->generate('type_index', ['versionSlug' => '__VERSION__']);
        $menu->addChild(
            'Types',
            [
                'uri' => $typesUri,
                'linkAttributes' => ['data-uri-template' => $typesUri],
            ]
        );
        $itemsUri = $this->urlGenerator->generate('item_index', ['versionSlug' => '__VERSION__']);
        $menu->addChild(
            'Items',
            [
                'uri' => $itemsUri,
                'linkAttributes' => [
                    'data-uri-template' => $itemsUri,
                ],
            ]
        );
        $locationsUri = $this->urlGenerator->generate('location_index', ['versionSlug' => '__VERSION__']);
        $menu->addChild(
            'Locations',
            [
                'uri' => $locationsUri,
                'linkAttributes' => ['data-uri-template' => $locationsUri],
            ]
        );
        $naturesUri = $this->urlGenerator->generate('nature_index', ['versionSlug' => '__VERSION__']);
        $menu->addChild(
            'Natures',
            [
                'uri' => '#',
                'linkAttributes' => ['data-uri-template' => $naturesUri],
            ]
        );
        $abilitiesUri = $this->urlGenerator->generate('ability_index', ['versionSlug' => '__VERSION__']);
        $menu->addChild(
            'Abilities',
            [
                'uri' => '#',
                'linkAttributes' => ['data-uri-template' => $abilitiesUri],
            ]
        );

        return $menu;
    }

    /**
     * Footer nav
     *
     * @param array $options
     *
     * @return ItemInterface
     */
    public function footerMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttribute('class', 'navbar-nav');

        $menu->addChild(
            'Credits',
            [
                'uri' => $this->urlGenerator->generate('page_credits'),
            ]
        );
        $menu->addChild(
            'Source',
            [
                'uri' => 'https://gitlab.com/gamestuff.info/poketools',
            ]
        );

        return $menu;
    }
}
