<?php

namespace Cms\BlockBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class BlockRegistriesPass
 * @package Cms\BlockBundle\DependencyInjection
 */
class BlockRegistriesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $blockRegistriesTag;

    /**
     * @var string
     */
    private $blockRegistryTag;

    /**
     * @param string $blockRegistriesTag
     * @param string $blockRegistryTag
     */
    public function __construct($blockRegistriesTag = 'block.registries', $blockRegistryTag = 'block.registry')
    {
        $this->blockRegistriesTag = $blockRegistriesTag;
        $this->blockRegistryTag = $blockRegistryTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->blockRegistriesTag)) {
            return;
        }

        $definition = $container->getDefinition($this->blockRegistriesTag);
        foreach ($container->findTaggedServiceIds($this->blockRegistryTag, true) as $serviceId => $tag) {
            $alias = $tag[0]['alias'] ?? null;
            if ($alias) {
                $definition->addMethodCall('addRegistry', [$alias, new Reference($serviceId)]);
            }
        }
    }
}
