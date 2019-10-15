<?php

namespace Cms\BlockBundle\DataTransformer;

use Cms\BlockBundle\Annotation\Entity;
use Cms\BlockBundle\Model\Entity\AbstractEntity;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\Finder\AnnotationsFinderInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;

class EntityTransformer extends AbstractBlockDataTransformer implements BlockDataTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AnnotationsFinderInterface
     */
    private $annotationsFinder;

    /**
     * EntityTransformer constructor.
     * @param EntityManagerInterface $entityManager
     * @param AnnotationsFinderInterface $annotationsFinder
     */
    public function __construct(EntityManagerInterface $entityManager, AnnotationsFinderInterface $annotationsFinder)
    {
        $this->entityManager = $entityManager;
        $this->annotationsFinder = $annotationsFinder;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        $class = $this->getClassName($value);
        $propertyValue = $value;
        if (is_object($value)) {
            if ($value instanceof BlockEntityInterface) {
                $propertyValue = $value->getId();
            } else {
                $propertyValue = $this->getPropertyValue($value);
            }
        }

        $newValue = $this->entityManager->getRepository($class)->findOneBy([
            $this->getPropertyName() => $propertyValue,
        ]);

        if ($value instanceof BlockEntityInterface && $newValue instanceof BlockEntityInterface) {
            $newValue->setName($value->getName());
        }

        return $newValue;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($value)
    {
        if (!is_object($value)) {
            return $value;
        }

        if (!$value instanceof BlockEntityInterface) {
            return $this->getPropertyValue($value);
        }

        return (new class() extends AbstractEntity {})
            ->setId($this->getPropertyValue($value))
            ->setName($value->getName())
        ;
    }

    /**
     * @inheritdoc
     */
    public function persist($value)
    {
        if (
            is_object($value)
            && !empty($this->annotationsFinder->findForClass($value, [\Doctrine\ORM\Mapping\Entity::class]))
            && in_array(__FUNCTION__, $this->annotation->cascade)
        ) {
            $this->entityManager->persist($value);

            $md = $this->entityManager->getClassMetadata(get_class($value));
            $this->entityManager->getUnitOfWork()->computeChangeSet($md, $value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function remove($value)
    {
        if (
            is_object($value)
            && !empty($this->annotationsFinder->findForClass($value, [\Doctrine\ORM\Mapping\Entity::class]))
            && in_array(__FUNCTION__, $this->annotation->cascade)
        ) {
            // attached entity to entity manager
            $entity = $this->entityManager->merge($value);
            $this->entityManager->remove($entity);
        }

        return $value;
    }

    /**
     * @param object $object
     * @return mixed|null
     */
    public function getClassName($object)
    {
        return $this->annotation->class ?? (is_object($object) ? get_class($object) : null);
    }

    /**
     * @return string|null
     */
    public function getPropertyName():? string
    {
        return $this->annotation->property ?? 'id';
    }

    /**
     * @param object $object
     * @return mixed|null
     */
    public function getPropertyValue(object $object)
    {
        $class = $this->getClassName($object);
        if (!$object instanceof $class) {
            return null;
        }

        $methodSuffix = Inflector::classify($this->getPropertyName());
        $methodName = sprintf('get%s', $methodSuffix);
        if (!method_exists($object, $methodName)) {
            $methodName = sprintf('is%s', $methodSuffix);
        }
        if (!method_exists($object, $methodName)) {
            return null;
        }

        return call_user_func_array([$object, $methodName], []);
    }

    /**
     * @inheritdoc
     */
    public function getAnnotations(): array
    {
        return [ Entity::class ];
    }
}