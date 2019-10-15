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
     * property used to find entity
     *
     * @var string
     */
    public $property = 'id';

    /**
     * @var array
     */
    public $cascade = ['persist'];
}