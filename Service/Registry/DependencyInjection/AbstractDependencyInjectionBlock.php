<?php

namespace Cms\BlockBundle\Service\Registry\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;

/**
 * Class AbstractDependencyInjectionBlock
 */
abstract class AbstractDependencyInjectionBlock implements DependencyInjectionBlockInterface
{
    /**
     * @var ContainerInterface
     */
    private $typeContainer;

    /**
     * @var array
     */
    private $classNames;

    /**
     * AbstractDependencyInjectionBlock constructor.
     * @param ContainerInterface $typeContainer
     * @param array $classNames
     */
    public function __construct(ContainerInterface $typeContainer, array $classNames)
    {
        $this->typeContainer = $typeContainer;
        $this->classNames = $classNames;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $name = $this->findClassName($name);
        if (!$this->typeContainer->has($name)) {
            throw new InvalidArgumentException(sprintf('The service "%s" is not registered in the service container.', $name));
        }

        return $this->typeContainer->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->typeContainer->has($this->findClassName($name));
    }

    /**
     * {@inheritdoc}
     */
    public function findClassName(string $name): string
    {
        return $this->classNames[$name] ?? $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassNames(): array
    {
        return $this->classNames;
    }
}
