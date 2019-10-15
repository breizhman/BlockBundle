<?php

namespace Cms\BlockBundle\Service\Registry;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Service\Registry\DependencyInjection\DependencyInjectionBlockInterface;
use Cms\BlockBundle\Service\ResolvedBlockTypeFactoryInterface;
use Cms\BlockBundle\Service\ResolvedBlockTypeInterface;

/**
 * Class BlockTypesRegistry
 * @package Cms\BlockBundle\Service\Registry
 */
class BlockTypesRegistry extends AbstractBlockRegistry
{
    /**
     * @var ResolvedBlockTypeFactoryInterface
     */
    private $factory;

    /**
     * @param DependencyInjectionBlockInterface $dependencyInjectionBlock
     * @param ResolvedBlockTypeFactoryInterface $factory
     */
    public function __construct(DependencyInjectionBlockInterface $dependencyInjectionBlock, ResolvedBlockTypeFactoryInterface $factory)
    {
        parent::__construct($dependencyInjectionBlock);
        $this->factory = $factory;
    }

    /**
     * @param BlockTypeInterface $type
     * @return ResolvedBlockTypeInterface
     */
    protected function resolve($type): ResolvedBlockTypeInterface
    {
        return $this->factory->createResolvedBlock($type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getInterfaceClassName(): string
    {
        return BlockTypeInterface::class;
    }
}