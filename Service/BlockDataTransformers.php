<?php

namespace BlockBundle\Service;

use BlockBundle\Annotation\BlockAnnotationInterface;
use BlockBundle\DataTransformer\BlockDataTransformerInterface;
use BlockBundle\Exception\NotFoundException;

/**
 * Class BlockDataTransformers
 */
class BlockDataTransformers implements BlockDataTransformersInterface
{
    /**
     * @var array
     */
    private $dataTransformers;

    /**
     * {@inheritdoc}
     */
    public function getDataTransformer(string $name): BlockDataTransformerInterface
    {
        if (!isset($this->dataTransformers[$name])) {
            throw new NotFoundException(sprintf('No block type found with name "%s".', $name));
        }

        return clone $this->dataTransformers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataTransformersByAnnotation(BlockAnnotationInterface $annotation, array $targets = []): array
    {
        $dataTransformers = [];
        $annotationClass = get_class($annotation);
        foreach ($this->dataTransformers as $name => $dataTransformer) {
            if (!$dataTransformer instanceof BlockDataTransformerInterface) {
                continue;
            }
            if (!in_array($annotationClass, $dataTransformer->getAnnotations())) {
                continue;
            }
            if (!empty($targets)
                && !empty($dataTransformer->getAnnotationTargets())
                && empty(array_intersect($dataTransformer->getAnnotationTargets(), $targets))
            ) {
                continue;
            }

            $dataTransformer->setAnnotation($annotation);
            $dataTransformers[] = clone $dataTransformer;
        }

        return $dataTransformers;
    }

    /**
     * {@inheritdoc}
     */
    public function addDataTransformer(string $alias, $dataTransformer): BlockDataTransformersInterface
    {
        $this->dataTransformers[$alias] = $dataTransformer;
        return $this;
    }
}
