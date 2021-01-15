<?php

namespace Cms\BlockBundle\DataTransformer;

use Cms\BlockBundle\Annotation\Type;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Doctrine\Common\Annotations\Annotation\Target;

class TypeTransformer extends AbstractBlockDataTransformer implements BlockDataTransformerInterface
{
    /**
     * @var Type
     */
    protected $annotation;

    /**
     * @var BlockFactoryInterface
     */
    private $blockFactory;

    /**
     * TypeTransformer constructor.
     * @param BlockFactoryInterface $blockFactory
     */
    public function __construct(BlockFactoryInterface $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (empty($value)) {
            return $value;
        }

        if ($value instanceof BlockEntityInterface) {
            return $value;
        }

        return $this->blockFactory->createEntity($this->annotation->name, (array) $value);
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        if (!(is_object($value) && $value instanceof BlockEntityInterface)) {
            return $value;
        }

        return $this->blockFactory->createDataFromEntity($value);
    }

    /**
     * @inheritdoc
     */
    public function persist($value)
    {
        if (is_object($value) && $value instanceof BlockEntityInterface) {
            $this->blockFactory->getEntityManager()->persist($value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function remove($value)
    {
        if (is_object($value) && $value instanceof BlockEntityInterface) {
            $this->blockFactory->getEntityManager()->remove($value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotations(): array
    {
        return [ Type::class ];
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationTargets(): array
    {
        return [Target::TARGET_PROPERTY];
    }
}