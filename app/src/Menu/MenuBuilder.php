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
        $menu->addChild('Pokèmon', ['uri' => '#']);
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
        $menu->addChild('Items', ['uri' => '#']);
        $menu->addChild('Locations', ['uri' => '#']);
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
}
