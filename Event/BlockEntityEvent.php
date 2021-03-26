<?php

namespace Cms\BlockBundle\Event;

use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class BlockEntityEvent
 *
 * @package Cms\BlockBundle\Event
 */
class BlockEntityEvent extends Event
{
    public const BUILD = 'block_entity.build';

    /**
     * @var BlockEntityInterface
     */
    protected $blockEntity;

    /**
     * BlockEntityBuildEvent constructor.
     *
     * @param BlockEntityInterface $blockEntity
     */
    public function __construct(BlockEntityInterface $blockEntity)
    {
        $this->blockEntity = $blockEntity;
    }

    /**
     * @return BlockEntityInterface
     */
    public function getBlockEntity(): BlockEntityInterface
    {
        return $this->blockEntity;
    }
}