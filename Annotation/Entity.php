<?php

namespace Cms\BlockBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "CLASS"})
 */
final class Entity implements BlockAnnotationInterface
{
    public const DEFAULT_PROPERTY = 'id';

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
    public $properties = [ self::DEFAULT_PROPERTY ];

    /**
     * @var array
     */
    public $cascade = ['persist'];
}