<?php

namespace BlockBundle\Service;

use BlockBundle\Annotation\BlockAnnotationInterface;
use BlockBundle\DataTransformer\BlockDataTransformerInterface;

interface BlockDataTransformersInterface
{
    /**
     * get transformer by name
     *
     * @param string $name
     * @return BlockDataTransformerInterface
     */
    public function getDataTransformer(string $name): BlockDataTransformerInterface;

    /**
     * get transformer by annotation
     *
     * @param BlockAnnotationInterface $annotation
     * @param array $targets
     * @return array
     */
    public function getDataTransformersByAnnotation(BlockAnnotationInterface $annotation, array $targets = []): array;

    /**
     * add transformer
     *
     * @param string $alias
     * @param mixed $dataTransformer
     * @return BlockDataTransformersInterface
     */
    public function addDataTransformer(string $alias, $dataTransformer): BlockDataTransformersInterface;
}