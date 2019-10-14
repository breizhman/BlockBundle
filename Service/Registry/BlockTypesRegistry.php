<?php

namespace BlockBundle\Service\Registry;

use BlockBundle\Model\Type\BlockTypeInterface;
use BlockBundle\Service\Registry\DependencyInjection\DependencyInjectionBlockInterface;
use BlockBundle\Service\ResolvedBlockTypeFactoryInterface;
use BlockBundle\Service\ResolvedBlockTypeInterface;

/**
 * Class BlockTypesRegistry
 * @package BlockBundle\Service\Registry
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