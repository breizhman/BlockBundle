<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Annotation\BlockAnnotationInterface;
use Cms\BlockBundle\DataTransformer\BlockDataTransformerInterface;
use Cms\BlockBundle\Exception\NotFoundException;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;

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
    public function getDataTransformersByAnnotation(BlockAnnotationInterface $annotation, array $targets = [], object $parentObject = null): array
    {
        $dataTransformers = [];
        $annotationClass = get_class($annotation);

        /** @var BlockDataTransformerInterface $dataTransformer */
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

            // pass parent block to transformer
            if ($parentObject instanceof BlockEntityInterface) {
                $dataTransformer->setParentBlockEntity($parentObject);
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
