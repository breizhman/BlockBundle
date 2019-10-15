<?php

namespace Cms\BlockBundle\Service\Entity;

use Cms\BlockBundle\Model\Entity\BlockEntityInterface;

interface BlockEntityTransformerInterface
{
    /**
     * transform block entity properties
     *
     * @param BlockEntityInterface $blockEntity
     * @param array $filterProperties
     * @param array $targets
     *
     * @return BlockEntityInterface
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function transform(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface;

    /**
     * reverse transform block entity properties
     *
     * @param BlockEntityInterface $blockEntity
     * @param array $filterProperties
     * @param array $targets
     *
     * @return BlockEntityInterface
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function reverseTransform(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface;

    /**
     * persist block entity properties
     *
     * @param BlockEntityInterface $blockEntity
     * @param array $filterProperties
     * @param array $targets
     *
     * @return BlockEntityInterface
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function persist(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface;

    /**
     * persist block entity properties
     *
     * @param BlockEntityInterface $blockEntity
     * @param array $filterProperties
     * @param array $targets
     *
     * @return BlockEntityInterface
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function remove(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface;
}