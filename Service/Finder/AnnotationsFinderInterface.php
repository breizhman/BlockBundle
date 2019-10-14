<?php

namespace BlockBundle\Service\Finder;

interface AnnotationsFinderInterface
{
    /**
     * get all annotations CLASS and PROPERTY from object
     *
     * @param object|string $object
     * @param array $filterAnnotations
     * @return array
     */
    public function findAll($object, $filterAnnotations = []): array;

    /**
     * get annotations CLASS from object
     *
     * @param object|string $object
     * @param array $filterAnnotations
     * @return array
     */
    public function findForClass(object $object, $filterAnnotations = []): array;

    /**
     * get All annotations PROPERTY from object
     *
     * @param object|string $object
     * @param array $filterAnnotations
     * @param array $filterPropertyNames
     * @return array
     */
    public function findForProperties(object $object, $filterAnnotations = [], $filterPropertyNames = []): array;

    /**
     * get annotations from property
     *
     * @param \ReflectionProperty $property
     * @param array $filterAnnotations
     * @return array
     */
    public function findForOneProperty(\ReflectionProperty $property, $filterAnnotations = []): array;

    /**
     * @param $object
     * @param string $propertyName
     * @param array $filterAnnotations
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function findForOnePropertyName($object, string $propertyName, $filterAnnotations = []): array;
}