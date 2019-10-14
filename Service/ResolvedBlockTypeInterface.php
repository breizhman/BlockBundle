<?php

namespace BlockBundle\Service;

use BlockBundle\Model\Type\BlockTypeInterface;

/**
 * Interface ResolvedBlockTypeInterface
 * @package BlockBundle\Service
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