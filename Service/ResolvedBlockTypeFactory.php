<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;

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