<?php

namespace Cms\BlockBundle\Service\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class BlockEntityProperty
 *
 * @package Cms\BlockBundle\Service\Entity
 */
class BlockEntityProperty implements BlockEntityPropertyInterface
{
    const STATE_ADD = 'add';
    const STATE_UPDATE = 'update';
    const STATE_DELETE = 'delete';
    const STATE_UNCHANGE = 'unchange';

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * BlockEntityProperty constructor.
     */
    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @inheritdoc
     */
    public function compare($oldValue, $newValue): string
    {
        if (is_object($oldValue) && is_object($newValue)) {

            // compare only properties of objects
            if ($newValue == $oldValue) {
                return static::STATE_UNCHANGE;
            }
        } else if ($newValue === $oldValue) {
            return static::STATE_UNCHANGE;
        }

        if ($oldValue === null) {
            return static::STATE_ADD;
        }

        if ($newValue === null) {
            return static::STATE_DELETE;
        }

        if (!(is_object($oldValue) || is_object($newValue))) {
            $oldValue = serialize($oldValue);
            $newValue = serialize($newValue);
        }

        if ($newValue !== $oldValue) {
            return static::STATE_UPDATE;
        }

        return static::STATE_UNCHANGE;
    }

    /**
     * @inheritdoc
     */
    public function setValue($objectOrArray, $propertyPath, $value)
    {
        $this->propertyAccessor->setValue($objectOrArray, $propertyPath, $value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue($objectOrArray, string $propertyPath)
    {
        if (is_object($objectOrArray)) {
            $reflectionProperty = new \ReflectionProperty($objectOrArray, $propertyPath);
            $reflectionProperty->setAccessible(true);
            return $reflectionProperty->getValue($objectOrArray);
        }

        return $this->propertyAccessor->getValue($objectOrArray, $propertyPath);
    }
}