<?php

namespace Cms\BlockBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class BlockExtension
 * @package Cms\BlockBundle\DependencyInjection
 */
class BlockExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('block.themes', $config['themes'] ?? []);

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/config'));
        $loader->load('services.yaml');
    }
}
