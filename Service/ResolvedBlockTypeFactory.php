<?php

namespace BlockBundle\Service;

use BlockBundle\Model\Type\BlockTypeInterface;

/**
 * Class ResolvedBlockTypeFactory
 * @package BlockBundle\Service
 */
class ResolvedBlockTypeFactory implements ResolvedBlockTypeFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResolvedBlock(BlockTypeInterface $type) : ResolvedBlockTypeInterface
    {
        return new ResolvedBlockType($type);
    }
}