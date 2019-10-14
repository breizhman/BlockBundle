<?php

namespace BlockBundle\Service\Entity;

use BlockBundle\Model\Entity\BlockEntityInterface;

interface BlockEntityManagerInterface
{
    /**
     * load block entity from data
     *
     * @param $blockEntityClass
     * @param array $data
     * @return BlockEntityInterface|null
     */
    public function load($blockEntityClass, array $data = []):? BlockEntityInterface;

    /**
     * convert block entity to array
     *
     * @param BlockEntityInterface $blockEntity
     * @return array|null
     */
    public function toArray(BlockEntityInterface $blockEntity):? array;

    /**
     * persist block entity data
     *
     * @param BlockEntityInterface $blockEntity
     * @param bool $indexation
     * @return BlockEntityManagerInterface
     */
    public function persist(BlockEntityInterface $blockEntity, bool $indexation = true): BlockEntityManagerInterface;

    /**
     * @param BlockEntityInterface $blockEntity
     * @return BlockEntityManagerInterface
     */
    public function persistIndexation(BlockEntityInterface $blockEntity): BlockEntityManagerInterface;

    /**
     * remove block entity data
     *
     * @param BlockEntityInterface $blockEntity
     * @return BlockEntityManagerInterface
     */
    public function remove(BlockEntityInterface $blockEntity): BlockEntityManagerInterface;

    /**
     * @param BlockEntityInterface $blockEntity
     * @return BlockEntityManagerInterface
     */
    public function removeIndexation(BlockEntityInterface $blockEntity): BlockEntityManagerInterface;

    /**
     * find one block entity by id
     *
     * @param mixed $id
     * @return BlockEntityInterface|null
     */
    public function findOneById($id):? BlockEntityInterface;

    /**
     * generate unique identify for block entity

     * @return string
     */
    public function generateId(): string;
    /**
     * @return BlockEntityProperty
     */
    public function getProperty(): BlockEntityProperty;
}