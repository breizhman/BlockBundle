<?php

namespace Cms\BlockBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "CLASS"})
 */
final class Type implements BlockAnnotationInterface
{
    /**
     * block type name
     *
     * @var string
     */
    public $name;
}