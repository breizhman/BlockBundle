<?php

namespace BlockBundle\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Collection implements BlockAnnotationInterface
{
    /**
     * block type names
     *
     * @var array
     */
    public $names = [];

    /**
     * block type groups
     *
     * @var array
     */
    public $groups = [];
}