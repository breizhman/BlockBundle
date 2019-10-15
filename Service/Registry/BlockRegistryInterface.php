<?php

namespace Cms\BlockBundle\Service\Registry;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Exception\InvalidArgumentException;

interface BlockRegistryInterface
{
    /**
     * Returns a form type by name.
     *
     * @param string $name The name of the type
     *
     * @return mixed
     *
     * @throws InvalidArgumentException if the type can not be retrieved from any extension
     */
    public function get($name);

    /**
     * Returns whether the given block type is supported.
     *
     * @param string $name The name of the type
     *
     * @return bool Whether the type is supported
     */
    public function has($name) : bool;

    /**
     * Returns all the block types.
     *
     * @return BlockTypeInterface[]
     */
    public function all() : array;

    /**
     * Returns all block name
     *
     * return array
     */
    public function getClassNames() : array;
}