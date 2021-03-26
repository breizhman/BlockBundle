<?php

namespace Cms\BlockBundle\Model\Entity;

/**
 * Interface BlockEntityInterface
 *
 * @package Cms\BlockBundle\Model\Entity
 */
interface BlockEntityInterface
{
    /**
     * get id of the block
     *
     * @return string
     */
    public function getBlockId();

    /**
     * set id of the block
     *
     * @param $id
     *
     * @return BlockEntityInterface
     */
    public function setBlockId($id): BlockEntityInterface;

    /**
     * get type of the block
     *
     * @return string
     */
    public function getBlockType();

    /**
     * set type of the block
     *
     * @param $type
     *
     * @return BlockEntityInterface
     */
    public function setBlockType($type): BlockEntityInterface;
}