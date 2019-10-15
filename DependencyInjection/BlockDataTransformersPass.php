<?php

namespace Cms\BlockBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds all services with the tags "block.type" as arguments of the "block.types" service.
 */
class BlockDataTransformersPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $blockDataTransformersTag;

    /**
     * @var string
     */
    private $blockDataTransformerTag;

    /**
     * @param string $blockDataTransformersTag
     * @param string $blockDataTransformerTag
     */
    public function __construct($blockDataTransformersTag = 'block.data_transformers', $blockDataTransformerTag = 'block.data_transformer')
    {
        $this->blockDataTransformersTag = $blockDataTransformersTag;
        $this->blockDataTransformerTag = $blockDataTransformerTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->blockDataTransformersTag)) {
            return;
        }

        $definition = $container->getDefinition($this->blockDataTransformersTag);
        foreach ($container->findTaggedServiceIds($this->blockDataTransformerTag, true) as $serviceId => $tag) {
            $alias = $tag[0]['alias'] ?? null;
            if ($alias) {
                $definition->addMethodCall('addDataTransformer', [$alias, new Reference($serviceId)]);
            }
        }
    }
}
