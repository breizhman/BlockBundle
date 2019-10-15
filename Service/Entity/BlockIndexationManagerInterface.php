<?php

namespace Cms\BlockBundle\Service\Entity;

use Cms\BlockBundle\Entity\BlockIndexation;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;

/**
 * Interface BlockIndexationManagerInterface
 * @package BlockBundle\Service\Entity
 */
interface  BlockIndexationManagerInterface
{
    /**
     * @param BlockEntityInterface $blockEntity
     * @param bool $autoFlush
     * @return BlockIndexationManagerInterface
     */
    public function persist(BlockEntityInterface $blockEntity, $autoFlush = false): BlockIndexationManagerInterface;

    /**
     * @param BlockEntityInterface $blockEntity
     * @param bool $autoFlush
     * @return BlockIndexationManagerInterface
     */
    public function remove(BlockEntityInterface $blockEntity, $autoFlush = false): BlockIndexationManagerInterface;

    /**
     * @param BlockEntityInterface $blockEntity
     * @return BlockIndexation|null
     */
    public function findByEntity(BlockEntityInterface $blockEntity) :? BlockIndexation;

    /**
     * @param string $id
     * @param string $name
     * @return BlockIndexation|null
     */
    public function findByIdAndName(string $id, string $name) :? BlockIndexation;
}