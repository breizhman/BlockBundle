<?php

namespace Cms\BlockBundle\DataTransformer;

use Cms\BlockBundle\Annotation\Type;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Doctrine\Common\Annotations\Annotation\Target;

class AssocEntityToTypeTransformer extends AbstractBlockDataTransformer implements BlockDataTransformerInterface
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
        return $this->addNameToEntity($value);
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        return $this->addNameToEntity($value);
    }

    /**
     * @inheritdoc
     */
    public function persist($value)
    {
        return $this->addNameToEntity($value);
    }


    /**
     * @inheritdoc
     */
    public function remove($value)
    {
        return $this->addNameToEntity($value);
    }

    /**
     * @param $value
     * @return null|BlockEntityInterface
     */
    protected function addNameToEntity($value):? BlockEntityInterface
    {
        if (!(is_object($value) && $value instanceof BlockEntityInterface)) {
            return null;
        }

        $blockType = $this->blockFactory->getType($this->annotation->name);
        if (!$blockType) {
            return null;
        }

        $value->setName($blockType->getName());
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
        return [Target::TARGET_CLASS];
    }
}