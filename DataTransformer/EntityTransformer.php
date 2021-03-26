<?php

namespace Cms\BlockBundle\DataTransformer;

use App\Entity\AdvertLocation;
use Cms\BlockBundle\Annotation\Entity;
use Cms\BlockBundle\Model\Entity\BlockEntity;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;

/**
 * Class EntityTransformer
 *
 * @package Cms\BlockBundle\DataTransformer
 * @property Entity $annotation
 */
class EntityTransformer extends AbstractBlockDataTransformer
{
    /**
     * @var BlockEntityManagerInterface
     */
    private $blockEntityManager;

    /**
     * EntityTransformer constructor.
     *
     * @param BlockEntityManagerInterface $blockEntityManager
     */
    public function __construct(BlockEntityManagerInterface $blockEntityManager)
    {
        $this->blockEntityManager = $blockEntityManager;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        $class = $this->getClassName($value);
        if (!$class) {
            return $value;
        }

        $propertiesValue = $this->getPropertiesWithValue($value);
        if (!$propertiesValue) {
            return null;
        }

        $newValue = $this->getEntityManager()->getRepository($class)->findOneBy($propertiesValue);
        if (!$newValue) {
            return null;
        }

        if (!$newValue instanceof BlockEntityInterface) {
            return $newValue;
        }

        if ($value instanceof BlockEntityInterface) {
            $newValue->setBlockId($value->getBlockId());
            $newValue->setBlockType($value->getBlockType());
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
            return $this->getPropertiesWithValue($value);
        }

        $class = $this->getClassName($value);

        /** @var BlockEntityInterface $blockEntity */
        $blockEntity = new $class;
        $blockEntity->setBlockId($value->getBlockId());
        $blockEntity->setBlockType($value->getBlockType());

        foreach ($this->getPropertiesWithValue($value) as $property => $propertyValue) {
            $this->blockEntityManager->getProperty()->setValue($blockEntity, $property, $propertyValue);
        }

        return $blockEntity;
    }

    /**
     * @inheritdoc
     */
    public function persist($value)
    {
        if (
            is_object($value)
            && $this->blockEntityManager->isEntity($value)
            && in_array(__FUNCTION__, $this->annotation->cascade, true)

            && !$this->getEntityManager()->contains($value)
            && $this->getEntityManager()->getUnitOfWork()->getSingleIdentifierValue($value) === null
        ) {
            $this->getEntityManager()->persist($value);

            $md = $this->getEntityManager()->getClassMetadata(get_class($value));
            $this->getEntityManager()->getUnitOfWork()->computeChangeSet($md, $value);
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
            && $this->blockEntityManager->isEntity($value)
            && in_array(__FUNCTION__, $this->annotation->cascade, true)
        ) {
            // attached entity to entity manager
            //$value = $this->getEntityManager()->merge($value);
            $this->getEntityManager()->remove($value);
        }

        return $value;
    }

    /**
     * @param object $object
     *
     * @return mixed|null
     */
    public function getClassName($object)
    {
        return $this->annotation->class ?? (is_object($object) ? get_class($object) : null);
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->annotation->properties ?? ['id'];
    }

    /**
     * @param mixed $valueOrObject
     *
     * @return array
     */
    public function getPropertiesWithValue($valueOrObject): array
    {
        $result = [];
        if (!is_object($valueOrObject)) {

            if (!is_array($valueOrObject)) {
                $valueOrObject = [$valueOrObject];
            }

            foreach ($this->getProperties() as $property) {
                if (!isset($valueOrObject[$property])) {
                    continue;
                }

                $result[$property] = $valueOrObject[$property];
            }

            return $result;
        }

        $class = $this->getClassName($valueOrObject);
        if (!$valueOrObject instanceof $class) {
            return $result;
        }

        foreach ($this->getProperties() as $property) {
            $methodSuffix = Inflector::classify($property);
            $methodName = sprintf('get%s', $methodSuffix);
            if (!method_exists($valueOrObject, $methodName)) {
                $methodName = sprintf('is%s', $methodSuffix);
            }
            if (!method_exists($valueOrObject, $methodName)) {
                continue;
            }

            $result[$property] = call_user_func_array([$valueOrObject, $methodName], []);
        }

        return $result;
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->blockEntityManager->getEntityManager();
    }

    /**
     * @inheritdoc
     */
    public function getAnnotations(): array
    {
        return [Entity::class];
    }
}