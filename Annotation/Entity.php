<?php

namespace Cms\BlockBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "CLASS"})
 */
final class Entity implements BlockAnnotationInterface
{
    /**
     * entity class name
     *
     * @var string
     */
    public $class = null;

    /**
     * properties used to find entity
     *
     * @var string[]
     */
    public $properties = ['id'];

    /**
     * @var array
     */
    public $cascade = ['persist'];
}