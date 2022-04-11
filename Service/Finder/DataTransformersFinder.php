<?php

namespace Cms\BlockBundle\Service\Finder;

use Cms\BlockBundle\Service\BlockDataTransformersInterface;
use Doctrine\Common\Annotations\Annotation\Target;

class DataTransformersFinder implements DataTransformersFinderInterface
{
    /**
     * @var AnnotationsFinderInterface
     */
    private $annotationsFinder;

    /**
     * @var BlockDataTransformersInterface
     */
    private $dataTransformers;

    /**
     * DataTransformersFinder constructor.
     * @param AnnotationsFinderInterface $annotationsFinder
     * @param BlockDataTransformersInterface $dataTransformers
     */
    public function __construct(AnnotationsFinderInterface $annotationsFinder, BlockDataTransformersInterface $dataTransformers)
    {
        $this->annotationsFinder = $annotationsFinder;
        $this->dataTransformers = $dataTransformers;
    }

    /**
     * @inheritdoc
     */
    public function findForClass(object $object, array $filterAnnotations = [], object $parentObject = null): array
    {
        $dataTransformers = [];
        $annotations = $this->annotationsFinder->findForClass($object, $filterAnnotations);

        foreach ($annotations as $annotation) {
            $dataTransformers = array_merge(
                $dataTransformers,
                $this->dataTransformers->getDataTransformersByAnnotation($annotation, [Target::TARGET_CLASS], $parentObject)
            );
        }

        return $dataTransformers;
    }

    /**
     * @inheritdoc
     */
    public function findForOneProperty(object $object, \ReflectionProperty $property, array $filterAnnotations = []): array
    {
        $dataTransformers = [];
        $annotations = $this->annotationsFinder->findForOneProperty($property, $filterAnnotations);

        foreach ($annotations as $annotation) {
            $dataTransformers = array_merge(
                $dataTransformers,
                $this->dataTransformers->getDataTransformersByAnnotation($annotation, [Target::TARGET_PROPERTY], $object)
            );
        }

        return $dataTransformers;
    }
}