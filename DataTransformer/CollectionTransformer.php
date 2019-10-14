<?php

namespace BlockBundle\DataTransformer;

use BlockBundle\Annotation\Collection;
use BlockBundle\Annotation\Type;
use BlockBundle\Collection\BlockCollection;
use BlockBundle\Model\Entity\BlockEntityInterface;
use BlockBundle\Service\BlockFactoryInterface;
use BlockBundle\Service\Finder\AnnotationsFinderInterface;

class CollectionTransformer extends AbstractBlockDataTransformer implements BlockDataTransformerInterface
{
    /**
     * @var Collection
     */
    protected $annotation;

    /**
     * @var BlockFactoryInterface
     */
    private $blockFactory;

    /**
     * @var TypeTransformer
     */
    private $typeTransformer;

    /**
     * @var AnnotationsFinderInterface
     */
    private $annotationsFinder;

    /**
     * CollectionTransformer constructor.
     * @param BlockFactoryInterface $blockFactory
     * @param TypeTransformer $typeTransformer
     * @param AnnotationsFinderInterface $annotationsFinder
     */
    public function __construct(BlockFactoryInterface $blockFactory, TypeTransformer $typeTransformer, AnnotationsFinderInterface $annotationsFinder)
    {
        $this->blockFactory = $blockFactory;
        $this->typeTransformer = $typeTransformer;
        $this->annotationsFinder = $annotationsFinder;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (!(is_array($value) || $value instanceof \Doctrine\Common\Collections\Collection)) {
            return null;
        }

        $value = $this->callTypeAnnotationFunc($value, 'transform');

        return new BlockCollection($this->blockFactory->getEntityManager(), $value);
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        if (!(is_array($value) || $value instanceof \Doctrine\Common\Collections\Collection)) {
            return null;
        }

        return $this->callTypeAnnotationFunc($value, 'reverseTransform');
    }

    /**
     * @inheritdoc
     */
    public function persist($value)
    {
        if (is_array($value) || $value instanceof \Doctrine\Common\Collections\Collection) {
            $this->callTypeAnnotationFunc($value, 'persist');
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function remove($value)
    {
        if (is_array($value) || $value instanceof \Doctrine\Common\Collections\Collection) {
            $this->callTypeAnnotationFunc($value, 'remove');
        }

        return $value;
    }

    /**
     * call type annotation method
     *
     * @param array|\Doctrine\Common\Collections\Collection $value
     * @param string $callbackFunc
     * @return array
     */
    protected function callTypeAnnotationFunc($value, string $callbackFunc)
    {
        $returnValues = [];
        foreach($value as $blockData) {

            $blockName = '';
            if (is_array($blockData) && isset($blockData['name'])) {
                $blockName = $blockData['name'];
            } else if ($blockData instanceof BlockEntityInterface && !empty($blockData->getName())) {
                $blockName = $blockData->getName();
            } else if (is_object($blockData)) {
                $annotationClass = $this->annotationsFinder->findForClass($blockData, [Type::class]);
                if ($annotationClass) {
                    $blockName = $annotationClass[0]->name;
                }
            }

            if (empty($blockName)) {
                continue;
            }
            if (!empty($this->annotation->names) && !in_array($blockName, $this->annotation->names)) {
                continue;
            }
            if (!empty($this->annotation->groups) && !in_array($this->blockFactory->getType($blockName)->getGroups(), $this->annotation->groups)) {
                continue;
            }

            $annotationType = new Type();
            $annotationType->name = $blockName;

            $returnValues[] = $this->typeTransformer
                ->setAnnotation($annotationType)
                ->{$callbackFunc}($blockData)
            ;
        }

        return $returnValues;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotations(): array
    {
        return [ Collection::class ];
    }
}