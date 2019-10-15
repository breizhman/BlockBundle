<?php

namespace Cms\BlockBundle\Service\Registry;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Exception\InvalidArgumentException;
use Cms\BlockBundle\Exception\ExceptionInterface;
use Cms\BlockBundle\Service\Registry\DependencyInjection\DependencyInjectionBlockInterface;

abstract class AbstractBlockRegistry implements BlockRegistryInterface
{
    /**
     * @var DependencyInjectionBlockInterface
     */
    private $dependencyInjectionBlock;

    /**
     * @var BlockTypeInterface[]
     */
    private $objects = array();

    /**
     * @param DependencyInjectionBlockInterface $dependencyInjectionBlock
     */
    public function __construct(DependencyInjectionBlockInterface $dependencyInjectionBlock)
    {
        $this->dependencyInjectionBlock = $dependencyInjectionBlock;
    }

    /**
     * {@inheritdoc}
     */
    abstract protected function getInterfaceClassName(): string;

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!isset($this->objects[$name])) {
            $object = null;
            if ($this->dependencyInjectionBlock->has($name)) {
                $object = $this->dependencyInjectionBlock->get($name);
            }

            if (!$object) {
                // Support fully-qualified class names
                if (!class_exists($name)) {
                    throw new InvalidArgumentException(sprintf('Could not load object "%s": class does not exist.', $name));
                }
                if (!is_subclass_of($name, $this->getInterfaceClassName())) {
                    throw new InvalidArgumentException(sprintf('Could not load object "%s": class does not implement "%s".', $name, $this->getInterfaceClassName()));
                }
                $object = new $name();
            }

            $this->objects[$name] = $this->resolve($object);
        }

        return $this->objects[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function has($name) : bool
    {
        if (isset($this->objects[$name])) {
            return true;
        }

        try {
            $this->get($name);
        } catch (ExceptionInterface $e) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $object
     * @return mixed
     */
    protected function resolve($object)
    {
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function all() : array
    {
        return $this->objects;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassNames() : array
    {
        return $this->dependencyInjectionBlock->getClassNames();
    }
}