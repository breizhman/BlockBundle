<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Exception\NotFoundException;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Model\Controller\BlockControllerInterface;
use Cms\BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

/**
 * Class BlockFactory
 *
 * @package Cms\BlockBundle\Service
 */
class BlockFactory implements BlockFactoryInterface
{
    /**
     * @var BlockRegistriesInterface
     */
    private $registries;

    /**
     * @var BlockEntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @param BlockRegistriesInterface    $registries
     * @param BlockEntityManagerInterface $entityManager
     * @param FormFactory                 $formFactory
     */
    public function __construct(BlockRegistriesInterface $registries, BlockEntityManagerInterface $entityManager, FormFactory $formFactory)
    {
        $this->registries = $registries;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(string $name): ?BlockTypeInterface
    {
        $registry = $this->registries->getRegistry('type');
        if ($registry->has($name)) {
            return $registry->get($name);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getVoter(string $name): ?string
    {
        $block = $this->getType($name);
        if ($block) {
            return $block->getVoter();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity(string $name): ?string
    {
        $block = $this->getType($name);
        if ($block) {
            return $block->getEntity();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(string $name): ?string
    {
        $block = $this->getType($name);
        if ($block) {
            return $block->getController();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm(string $name, BlockEntityInterface $entity, $options): ?FormInterface
    {
        $block = $this->getType($name);
        if ($block) {
            return $this->formFactory->create($block->getFormType(), $entity, $options);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createController(string $name): ?BlockControllerInterface
    {
        $className = $this->getController($name);
        if ($className) {
            $registry = $this->registries->getRegistry('controller');
            if ($registry->has($className)) {
                return $registry->get($className);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function loadEntity(string $id): ?BlockEntityInterface
    {
        return $this->entityManager->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity(string $name, array $data = []): ?BlockEntityInterface
    {
        return $this->entityManager->create($name, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function createDataFromEntity(BlockEntityInterface $entity): array
    {
        return $this->entityManager->toArray($entity);
    }

    /**
     * @return BlockRegistriesInterface
     */
    public function getRegistries(): BlockRegistriesInterface
    {
        return $this->registries;
    }

    /**
     * @return BlockEntityManagerInterface
     */
    public function getEntityManager(): BlockEntityManagerInterface
    {
        return $this->entityManager;
    }
}