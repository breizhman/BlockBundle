<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Exception\NotFoundException;
use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Service\Registry\BlockRegistryInterface;

/**
 * Class BlockRegistries
 */
class BlockRegistries implements BlockRegistriesInterface
{
    /**
     * @var array
     */
    private $registries;

    /**
     * {@inheritdoc}
     */
    public function getRegistry(string $name): BlockRegistryInterface
    {
        if (!isset($this->registries[$name])) {
            throw new NotFoundException(sprintf('No block registry found with name "%s".', $name));
        }

        return $this->registries[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function addRegistry(string $alias, $registry): BlockRegistriesInterface
    {
        $this->registries[$alias] = $registry;

        return $this;
    }
}
