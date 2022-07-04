<?php

namespace Cms\BlockBundle\Service\Entity;

use Cms\BlockBundle\Exception\NotFoundException;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Doctrine\ORM\EntityManagerInterface;

interface BlockEntityManagerInterface
{
    /**
     * create block entity with data
     *
     * @param string $nameOrClass
     * @param array  $data
     * @param null|BlockEntityInterface  $parentBlock
     *
     * @return BlockEntityInterface|null
     */
    public function create(string $nameOrClass, array $data = [], BlockEntityInterface $parentBlock = null): ?BlockEntityInterface;

    /**
     * load block entity by ID and name
     *
     * @param string $id
     *
     * @return BlockEntityInterface|null
     *
     * @throws NotFoundException
     */
    public function load(string $id): ?BlockEntityInterface;

    /**
     * @param BlockEntityInterface $blockEntity
     */
    public function register(BlockEntityInterface $blockEntity): void;

    /**
     * convert block entity to array
     *
     * @param BlockEntityInterface $blockEntity
     *
     * @return array|null
     */
    public function toArray(BlockEntityInterface $blockEntity): ?array;

    /**
     * persist block entity data
     *
     * @param BlockEntityInterface $blockEntity
     * @param null|BlockEntityInterface $parentBlockEntity
     *
     * @return BlockEntityManagerInterface
     */
    public function persist(BlockEntityInterface $blockEntity, BlockEntityInterface $parentBlockEntity = null): BlockEntityManagerInterface;

    /**
     * remove block entity data
     *
     * @param BlockEntityInterface $blockEntity
     *
     * @return BlockEntityManagerInterface
     */
    public function remove(BlockEntityInterface $blockEntity): BlockEntityManagerInterface;

    /**
     * flush block entities
     *
     * @return BlockEntityManagerInterface
     */
    public function flush(): BlockEntityManagerInterface;

    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return bool
     */
    public function hasChanged(BlockEntityInterface $blockEntity): bool;

    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return bool
     */
    public function isNew(BlockEntityInterface $blockEntity): bool;

    /**
     * @param BlockEntityInterface|string $blockEntity block entity or block id
     *
     * @return void
     */
    public function flagAsLoading($blockEntity): void;

    /**
     * @param BlockEntityInterface|string $blockEntity block entity or block id
     *
     * @return bool
     */
    public function isLoading($blockEntity): bool;

    /**
     * @param BlockEntityInterface|string $blockEntity block entity or block id
     *
     * @return bool
     */
    public function isLoaded($blockEntity): bool;

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isEntity(object $entity): bool;

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface;

    /**
     * @return BlockEntityProperty
     */
    public function getProperty(): BlockEntityProperty;
}