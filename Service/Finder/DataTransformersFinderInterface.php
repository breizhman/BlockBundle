<?php

namespace Cms\BlockBundle\Service\Finder;


interface DataTransformersFinderInterface
{
    /**
     * find transformers for class
     *
     * @param object $object
     * @param array $filterAnnotations
     * @return array
     */
    public function findForClass(object $object, array $filterAnnotations = []): array;

    /**
     * find transformers for property
     *
     * @param \ReflectionProperty $property
     * @param array $filterAnnotations
     * @return array
     */
    public function findForOneProperty(\ReflectionProperty $property, array $filterAnnotations = []): array;
}