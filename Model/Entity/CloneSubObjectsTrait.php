<?php

namespace Cms\BlockBundle\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Trait CloneSubObjectsTrait
 *
 * @package App\Entity
 */
trait CloneSubObjectsTrait
{
    /**
     * on clone, clone sub objects
     */
    public function __clone()
    {
        foreach ($this as $key => $val) {
            $this->{$key} = $this->cloneValue($val);
        }
    }

    /**
     * clone one value
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function cloneValue($value)
    {
        if (is_object($value)) {
            return $this->cloneObject($value);
        }

        if (is_array($value)) {
            return $this->cloneArray($value);
        }

        return $value;
    }

    /**
     * @param array $value
     *
     * @return array
     */
    protected function cloneArray(array $value): array
    {
        return unserialize(serialize($value));
    }

    /**
     * @param object $value
     *
     * @return object
     */
    protected function cloneObject($value)
    {
        return clone $value;
    }

    /**
     * @param Collection $value
     *
     * @return Collection
     */
    protected function cloneCollection(Collection $value): Collection
    {
        $newVal = new ArrayCollection();
        foreach ($value->toArray() as $subVal) {
            $newVal->add($this->cloneValue($subVal));
        }

        return $newVal;
    }
}