<?php

namespace BlockBundle\DataTransformer;

use BlockBundle\Annotation\BlockAnnotationInterface;

interface BlockDataTransformerInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function transform($value);

    /**
     * @param $value
     * @return mixed
     */
    public function reverseTransform($value);

    /**
     * @param $value
     * @return mixed
     */
    public function persist($value);

    /**
     * @param $value
     * @return mixed
     */
    public function remove($value);

    /**
     * get annotation class name, associated with current data transformer
     *
     * @return string
     */
    public function getAnnotations(): array;

    /**
     * @return array
     */
    public function getAnnotationTargets(): array;

    /**
     * get annotation, associated with current data transformer
     *
     * @return BlockAnnotationInterface
     */
    public function getAnnotation(): BlockAnnotationInterface;

    /**
     * set annotation, associated with current data transformer
     *
     * @param BlockAnnotationInterface $annotation
     * @return BlockDataTransformerInterface
     */
    public function setAnnotation(BlockAnnotationInterface $annotation): BlockDataTransformerInterface;
}