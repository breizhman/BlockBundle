<?php

namespace BlockBundle\Service;

use BlockBundle\Annotation\Collection;
use BlockBundle\Annotation\Type;
use BlockBundle\Model\Type\BlockTypeInterface;
use BlockBundle\Service\Finder\AnnotationsFinderInterface;
use BlockBundle\Service\Registry\BlockRegistryInterface;

/**
 * Class AbstractBlockForms
 * @package BlockBundle\Service
 */
abstract class AbstractBlockForms implements BlockFormsInterface
{
    /**
     * @var BlockRegistryInterface
     */
    protected $blockRegistry;

    /**
     * @var AnnotationsFinderInterface
     */
    protected $annotationsFinder;

    /**
     * @var array
     */
    protected $blockGroups = [];

    /**
     * @var array
     */
    protected $blockNames = [];

    /**
     * AbstractBlockForms constructor.
     * @param BlockRegistryInterface $blockRegistry
     * @param AnnotationsFinderInterface $annotationsFinder
     */
    public function __construct(BlockRegistryInterface $blockRegistry, AnnotationsFinderInterface $annotationsFinder)
    {
        $this->blockRegistry = $blockRegistry;
        $this->annotationsFinder = $annotationsFinder;
    }

    /**
     * @inheritdoc
     */
    public function load(array $options = []): array
    {
        $forms = [];
        if (isset($options['entity'], $options['entity']['class'], $options['entity']['property'])) {
            $annotations = $this->findByEntityProperty($options['entity']['class'], $options['entity']['property']);
            if ($annotations) {
                $options = $annotations;
            }
        }

        $blockNames  = $options['names']  ?? $this->getBlockNames();
        $blockGroups = $options['groups'] ?? $this->getBlockGroups();

        // need selection blocks by name or group
        if (empty($blockNames) && empty($blockGroups)) {
            return [];
        }

        foreach ($this->blockRegistry->getClassNames() as $blockName => $blockFormClass) {

            $addForm = false;
            if (in_array($blockName, $blockNames) || in_array($blockFormClass, $blockNames)) {
                $addForm = true;
            }

            $block = $this->findByName($blockFormClass);
            if (!$addForm) {
                if (array_intersect($block->getGroups(), $blockGroups)) {
                    $addForm = true;
                }
            }

            if ($addForm) {
                $forms[$block->getName()] = $block->getFormType();
            }
        }

        return $forms;
    }

    /**
     * @inheritdoc
     */
    public function findByName(string $blockName):? BlockTypeInterface
    {
        return $this->blockRegistry->get($blockName);
    }

    /**
     * @param string $entityClass
     * @param string $propertyName
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function findByEntityProperty(string $entityClass, string $propertyName): array
    {
        $data = [];
        $annotations =  $this->annotationsFinder->findForOnePropertyName($entityClass, $propertyName, [Type::class, Collection::class]);
        if ($annotations) {
            foreach ($annotations as $annotation) {
                $data = array_merge($data, (array) $annotation);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getBlockGroups(): array
    {
        return $this->blockGroups;
    }

    /**
     * @param array $blockGroups
     * @return AbstractBlockForms
     */
    public function setBlockGroups(array $blockGroups): AbstractBlockForms
    {
        $this->blockGroups = $blockGroups;
        return $this;
    }

    /**
     * @return array
     */
    public function getBlockNames(): array
    {
        return $this->blockNames;
    }

    /**
     * @param array $blockNames
     * @return AbstractBlockForms
     */
    public function setBlockNames(array $blockNames): AbstractBlockForms
    {
        $this->blockNames = $blockNames;
        return $this;
    }
}