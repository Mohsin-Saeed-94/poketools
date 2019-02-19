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
        $menu = $this->factory->createItem('root')
            ->setChildrenAttribute('class', 'navbar-nav');
        $menu->addChild('Pokèmon', ['uri' => '#']);
        $menu->addChild('Moves', ['uri' => '#']);
        $menu->addChild('Types', ['uri' => '#']);
        $menu->addChild('Items', ['uri' => '#']);
        $menu->addChild('Locations', ['uri' => '#']);
        $menu->addChild('Natures', ['uri' => '#']);
        $abilitiesUri = $this->urlGenerator->generate('ability_index', ['version_slug' => '__VERSION__']);
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
