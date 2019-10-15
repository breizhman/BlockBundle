<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;

interface ResolvedBlockTypeFactoryInterface
{
    /**
     * create and return ResolvedBlockTypeInterface instance
     *
     * @param BlockTypeInterface $block
     *
     * @return ResolvedBlockTypeInterface
     */
    public function createResolvedBlock(BlockTypeInterface $block) : ResolvedBlockTypeInterface;
}