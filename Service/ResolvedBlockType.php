<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Exception\ClassNotFoundException;
use Doctrine\Inflector\Inflector;

/**
 * Class ResolvedBlockType
 * @package Cms\BlockBundle\Service
 */
class ResolvedBlockType implements BlockTypeInterface, ResolvedBlockTypeInterface
{
    /**
     * @var BlockTypeInterface
     */
    private $innerType;

    /**
     * ResolvedBlockType constructor.
     * @param BlockTypeInterface $blockType
     */
    public function __construct(BlockTypeInterface $blockType)
    {
        $this->innerType = $blockType;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->innerType->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups(): array
    {
        return $this->innerType->getGroups();
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerType(): BlockTypeInterface
    {
        return $this->innerType;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity():? string
    {
        $className = $this->getInnerType()->getEntity();
        if (!$className) {
            $className = $this->constructNamespace('{base}\\Entity\\{name}');
        }

        if (!class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        return $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType():? string
    {
        $className = $this->getInnerType()->getFormType();
        if (!$className) {
            $className = $this->constructNamespace('{base}\\Form\\{name}Type');
        }

        if (!class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        return $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getController():? string
    {
        $className = $this->getInnerType()->getController();
        if (!$className) {
            $className = $this->constructNamespace('{base}\\Controller\\{name}Controller');
        }

        if (!class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        return $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getVoter():? string
    {
        $className = $this->getInnerType()->getVoter();
        if (!$className) {
            $className = $this->constructNamespace('{base}\\Voter\\{name}Voter');
        }

        if (!class_exists($className)) {
            throw new ClassNotFoundException($className);
        }

        return $className;
    }

    /**
     * @param string $template
     * @param bool $doubleSlash
     *
     * @return string|null
     */
    protected function constructNamespace(string $template, bool $doubleSlash = true):? string
    {
        $baseName = $this->findParentNamespace(get_class($this->getInnerType()), $doubleSlash, 1);
        $className = str_replace(['{base}', '{name}'], [$baseName,  Inflector::classify($this->getName())], $template);

        return $className;
    }

    /**
     * @param string $namespace
     * @param bool $doubleSlash
     * @param int $level
     *
     * @return null|string
     */
    protected function findParentNamespace(string $namespace, bool $doubleSlash = true, int $level = 1):? string
    {
        preg_match("/(?P<dirname>.+)(\\\\.+)$/i", $namespace, $matches);
        if (isset($matches['dirname'])) {
            $dirname = $matches['dirname'];
            if ($level > 0) {
                $dirname = $this->findParentNamespace($dirname, $level-1, false);
            }
            if ($doubleSlash) {
                $dirname = str_replace('/', '//', $dirname);
            }

            return $dirname;
        }

        return null;
    }
}