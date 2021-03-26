<?php

namespace Cms\BlockBundle\EventListener;

use Cms\BlockBundle\Service\BlockFactoryInterface;

class BlockFactoryAccessorListener
{
    /**
     * @var BlockFactoryInterface
     */
    private $blockFactory;

    /**
     * BlockFactoryAccessorListener constructor.
     *
     * @param BlockFactoryInterface $blockFactory
     */
    public function __construct(BlockFactoryInterface $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return BlockFactoryInterface
     */
    public function getBlockFactory(): BlockFactoryInterface
    {
        return $this->blockFactory;
    }
}