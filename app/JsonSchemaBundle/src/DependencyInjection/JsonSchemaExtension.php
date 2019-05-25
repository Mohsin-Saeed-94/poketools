<?php
/**
 * @file JsonSchemaExtension.php
 */

namespace DragoonBoots\JsonSchemaBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class JsonSchemaExtension
 */
class JsonSchemaExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $schemaPathLoader = $container->getDefinition('json_schema.loader.path_loader');
        $container->setParameter('json_schema.schema_path', $config['schemas']['path']);
        $container->setParameter('json_schema.schema_prefix', $config['schemas']['prefix']);
    }
}
