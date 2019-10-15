<?php

namespace Cms\BlockBundle\DataTransformer;

use Cms\BlockBundle\Annotation\BlockAnnotationInterface;

abstract class AbstractBlockDataTransformer implements BlockDataTransformerInterface
{
    /**
     * @var BlockAnnotationInterface
     */
    protected $annotation;

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function persist($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function remove($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotation(): BlockAnnotationInterface
    {
        return $this->annotation;
    }

    /**
     * @inheritdoc
     */
    public function setAnnotation(BlockAnnotationInterface $annotation): BlockDataTransformerInterface
    {
        $this->annotation = $annotation;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationTargets(): array
    {
        return [];
    }
}