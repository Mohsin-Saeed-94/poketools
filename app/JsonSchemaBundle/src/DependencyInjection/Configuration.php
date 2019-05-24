<?php
/**
 * @file Configuration.php
 */

namespace DragoonBoots\JsonSchemaBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('json_schema');

        // @formatter:off
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('schemas')
                    ->children()
                        ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('prefix')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
