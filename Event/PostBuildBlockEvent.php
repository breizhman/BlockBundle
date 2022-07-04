<?php

namespace Cms\BlockBundle\Event;

use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PostBuildBlockEvent
 *
 * @package Cms\BlockBundle\Event
 */
class PostBuildBlockEvent extends Event
{
    public const POST_BUILD = 'block_entity.build.post';

    /**
     * @var BlockEntityInterface
     */
    protected $blockEntity;

    /**
     * PostBuildBlockEvent constructor.
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