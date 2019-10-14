<?php

namespace BlockBundle\Service\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class BlockEntityProperty
 * @package BlockBundle\Service\Entity
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
        if (!is_null($oldValue)) {
            if (!is_null($newValue)) {

                if (!(is_object($oldValue) || is_object($oldValue))) {
                    $oldValue = serialize($oldValue);
                    $newValue = serialize($newValue);
                }

                if ($newValue === $oldValue) {
                    return static::STATE_UNCHANGE;
                } else {
                    return static::STATE_UPDATE;
                }
            } else {
                return static::STATE_DELETE;
            }
        } else {
            return static::STATE_ADD;
        }
    }

    /**
     * @inheritdoc
     */
    public function setValue($objectOrArray, $propertyPath, $value)
    {
        try {
            $this->propertyAccessor->setValue($objectOrArray, $propertyPath, $value);
        } catch(\Throwable $t) {

        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue($objectOrArray, string $propertyPath)
    {
        try {
            return $this->propertyAccessor->getValue($objectOrArray, $propertyPath);
        } catch(\Throwable $t) {
            return null;
        }
    }
}