<?php

namespace BlockBundle\Service;

use BlockBundle\Service\Registry\BlockRegistryInterface;

interface BlockRegistriesInterface
{
    /**
     * get registry by name
     *
     * @param string $name
     * @return BlockRegistryInterface
     */
    public function getRegistry(string $name): BlockRegistryInterface;

    /**
     * add registry
     *
     * @param string $alias
     * @param mixed $registry
     * @return BlockRegistriesInterface
     */
    public function addRegistry(string $alias, $registry): BlockRegistriesInterface;
}