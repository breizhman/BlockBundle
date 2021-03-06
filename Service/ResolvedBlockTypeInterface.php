<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;

/**
 * Interface ResolvedBlockTypeInterface
 * @package Cms\BlockBundle\Service
 */
interface ResolvedBlockTypeInterface
{
    /**
     * get BlockTypeInterface instance initial
     *
     * @return BlockTypeInterface
     */
    public function getInnerType(): BlockTypeInterface;
}