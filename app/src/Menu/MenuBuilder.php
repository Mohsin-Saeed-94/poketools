<?php
/**
 * @file MenuBuilder.php
 */

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * Main Menu Builder
 */
class MenuBuilder
{
    private $factory;

    /**
     * MenuBuilder constructor.
     *
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
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
        $menu->addChild('Abilities', ['uri' => '#']);

        return $menu;
    }
}
