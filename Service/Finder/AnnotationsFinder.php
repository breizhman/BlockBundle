<?php

namespace Cms\BlockBundle\Service\Finder;

use Cms\BlockBundle\Annotation\BlockAnnotationInterface;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationsFinder implements AnnotationsFinderInterface
{
    /**
     * @inheritdoc
     */
    public function findAll($object, $filterAnnotations = []): array
    {
        $classAnnotations      = $this->findForClass($object, $filterAnnotations);
        $propertiesAnnotations = $this->findForProperties($object, $filterAnnotations);

        if ($classAnnotations || $propertiesAnnotations) {
            return [
                'class' => $this->getReflectionClass($object),
                'annotations' => $classAnnotations,
                'properties' => $propertiesAnnotations,
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function findForClass($object, $filterAnnotations = []): array
    {
        if (!$object instanceof \ReflectionClass) {
            $object = $this->getReflectionClass($object);
        }

        $annotations = (new AnnotationReader())->getClassAnnotations($object);

        return $this->filter($annotations, $filterAnnotations);
    }

    /**
     * @inheritdoc
     */
    public function findForProperties($object, $filterAnnotations = [], $filterPropertyNames = []): array
    {
        $annotations = [];

        if (!$object instanceof \ReflectionClass) {
            $object = $this->getReflectionClass($object);
        }

        foreach ($object->getProperties() as $property) {
            if (empty($filterProperties) || in_array($property->getName(), $filterProperties)) {
                $currentAnnotations = $annotations[$property->getName()]['annotations'] ?? [];

                $newAnnotations = $this->findForOneProperty($property, $filterAnnotations);

                $annotations[$property->getName()] = [
                    'property' => $property,
                    'annotations' => array_merge($currentAnnotations, $newAnnotations)
                ];
            }
        }

        return $annotations;
    }

    /**
     * @inheritdoc
     */
    public function findForOneProperty(\ReflectionProperty $property, $filterAnnotations = []): array
    {
        $annotations = (new AnnotationReader())->getPropertyAnnotations($property);

        return $this->filter($annotations, $filterAnnotations);
    }

    /**
     * @inheritdoc
     */
    public function findForOnePropertyName($object, string $propertyName, $filterAnnotations = []): array
    {
        $property = $this->getReflectionProperty($object, $propertyName);
        if ($property) {
            $annotations = (new AnnotationReader())->getPropertyAnnotations($property);

            return $this->filter($annotations, $filterAnnotations);
        }

        return [];
    }

    /**
     * @param array $annotations
     * @param array $filterAnnotations
     * @return array
     */
    private function filter(array $annotations, array $filterAnnotations = [])
    {
        $annotations = array_filter($annotations, function($annotation) use ($filterAnnotations) {
            if (empty($filterAnnotations)) {
                return ($annotation instanceof BlockAnnotationInterface);
            } else {
                return  in_array(get_class($annotation), $filterAnnotations);
            }
        });

        return $annotations;
    }

    /**
     * @param object|string $object
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    private function getReflectionClass($object)
    {
        return ( new \ReflectionClass(is_object($object) ? get_class($object) : $object) );
    }

    /**
     * @param $object
     * @param string $propertyName
     * @return null|\ReflectionProperty
     * @throws \ReflectionException
     */
    private function getReflectionProperty($object, string $propertyName)
    {
        $reflectionClass = $this->getReflectionClass($object);
        if ($reflectionClass) {
            foreach ($reflectionClass->getProperties() as $property) {
                if ($property->getName() === $propertyName) {
                    return $property;
                }
            }
        }

        return null;
    }
}