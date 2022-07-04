<?php

namespace Cms\BlockBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PreBuildBlockEvent
 *
 * @package Cms\BlockBundle\Event
 */
class PreBuildBlockEvent extends Event
{
    public const PRE_BUILD = 'block_entity.build.pre';

    /**
     * @var array
     */
    protected $blockData;

    /**
     * @param array $blockData
     */
    public function __construct(array $blockData)
    {
        $this->blockData = $blockData;
    }

    /**
     * @return array
     */
    public function getBlockData(): array
    {
        return $this->blockData;
    }
}