<?php

namespace Cms\BlockBundle\Service\Entity;

interface BlockEntityPropertyInterface
{
    /**
     * compare old and new value property and return status
     *
     * @param mixed $oldValue
     * @param mixed $newValue
     *
     * @return string
     */
    public function compare($oldValue, $newValue): string;

    /**
     * @param object|array $objectOrArray
     * @param string $propertyPath
     * @param mixed $value
     * @return $this
     */
    public function setValue($objectOrArray, $propertyPath, $value);

    /**
     * @param object|array $objectOrArray
     * @param string $propertyPath
     * @return null|mixed
     */
    public function getValue($objectOrArray, string $propertyPath);
}