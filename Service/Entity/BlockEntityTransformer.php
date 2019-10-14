<?php

namespace BlockBundle\Service\Entity;

use BlockBundle\Model\Entity\BlockEntityInterface;
use BlockBundle\DataTransformer\BlockDataTransformerInterface;
use BlockBundle\Service\Finder\DataTransformersFinderInterface;
use Doctrine\Common\Annotations\Annotation\Target;

class BlockEntityTransformer implements BlockEntityTransformerInterface
{
    /**
     * @var BlockEntityPropertyInterface
     */
    protected $property;

    /**
     * @var DataTransformersFinderInterface
     */
    protected $dataTransformersFinder;

    /**
     * BlockEntityTransformer constructor.
     * @param BlockEntityPropertyInterface $property
     * @param DataTransformersFinderInterface $dataTransformersFinder
     */
    public function __construct(BlockEntityPropertyInterface $property, DataTransformersFinderInterface $dataTransformersFinder)
    {
        $this->property = $property;
        $this->dataTransformersFinder = $dataTransformersFinder;
    }

    /**
     * @inheritdoc
     */
    public function transform(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface
    {
        return $this->runDataTransformers($blockEntity, function ($dataTransformer, $value) {
            /** @var BlockDataTransformerInterface $dataTransformer */
            return $dataTransformer->transform($value);
        }, $filterProperties, $targets);
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface
    {
        return $this->runDataTransformers($blockEntity, function ($dataTransformer, $value) {
            /** @var BlockDataTransformerInterface $dataTransformer */
            return $dataTransformer->reverseTransform($value);
        }, $filterProperties, $targets);
    }

    /**
     * @inheritdoc
     */
    public function persist(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface
    {
        return $this->runDataTransformers($blockEntity, function ($dataTransformer, $value) {
            /** @var BlockDataTransformerInterface $dataTransformer */
            return $dataTransformer->persist($value);
        }, $filterProperties, $targets);
    }

    /**
     * @inheritdoc
     */
    public function remove(BlockEntityInterface $blockEntity, array $filterProperties = [], array $targets = []): BlockEntityInterface
    {
        return $this->runDataTransformers($blockEntity, function ($dataTransformer, $value) {
            /** @var BlockDataTransformerInterface $dataTransformer */
            return $dataTransformer->remove($value);
        }, $filterProperties, $targets);
    }

    /**
     * run block data transformer on bloc entity property
     *
     * @param BlockEntityInterface $blockEntity
     * @param $callDataTransformFunc
     * @param array $filterProperties
     * @param array $targets
     *
     * @return BlockEntityInterface
     *
     * @throws \ReflectionException
     */
    protected function runDataTransformers(BlockEntityInterface $blockEntity, $callDataTransformFunc, array $filterProperties = [], array $targets = []): BlockEntityInterface
    {
        if (empty($targets) || in_array(Target::TARGET_CLASS, $targets)) {
            $blockEntity = $this->runDataTransformersForClass($blockEntity, $callDataTransformFunc);
        }

        if (empty($targets) || in_array(Target::TARGET_PROPERTY, $targets)) {
            $blockEntity = $this->runDataTransformersForProperty($blockEntity, $callDataTransformFunc, $filterProperties);
        }

        return $blockEntity;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param $callDataTransformFunc
     * @return BlockEntityInterface
     */
    protected function runDataTransformersForClass(BlockEntityInterface $blockEntity, $callDataTransformFunc): BlockEntityInterface
    {
        $dataTransformers = $this->dataTransformersFinder->findForClass($blockEntity);

        if ($dataTransformers) {
            /** @var BlockDataTransformerInterface $dataTransformer */
            foreach ($dataTransformers as $dataTransformer) {
                $blockEntity = call_user_func_array($callDataTransformFunc, [$dataTransformer, $blockEntity]);
            }
        }

        return $blockEntity;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param $callDataTransformFunc
     * @param array $filterProperties
     * @return BlockEntityInterface
     * @throws \ReflectionException
     */
    protected function runDataTransformersForProperty(BlockEntityInterface $blockEntity, $callDataTransformFunc, array $filterProperties = []): BlockEntityInterface
    {
        $properties = $this->getReflectionProperties($blockEntity);
        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {

            if (empty($filterProperties) || in_array($property->getName(), $filterProperties)) {

                $value = $this->property->getValue($blockEntity, $property->getName());
                $dataTransformers = $this->dataTransformersFinder->findForOneProperty($property);

                if ($dataTransformers) {
                    /** @var BlockDataTransformerInterface $dataTransformer */
                    foreach ($dataTransformers as $dataTransformer) {
                        $value = call_user_func_array($callDataTransformFunc, [$dataTransformer, $value]);
                    }

                    $this->property->setValue($blockEntity, $property->getName(), $value);
                }
            }
        }

        return $blockEntity;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param array $filterProperties
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function getReflectionProperties(BlockEntityInterface $blockEntity, $filterProperties = []): array
    {
        $properties = ( new \ReflectionClass(get_class($blockEntity)) )->getProperties();

        if ($filterProperties) {
            $properties = array_filter($properties, function($property) use ($filterProperties) {
                /** @var \ReflectionProperty $property */
                return in_array($property->getName(), $filterProperties);
            });
        }

        return $properties;
    }
}