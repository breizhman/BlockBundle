<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Model\Controller\BlockControllerInterface;
use Cms\BlockBundle\Exception\ClassNotFoundException;
use Cms\BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Symfony\Component\Form\FormInterface;

interface BlockFactoryInterface
{
    /**
     * get BlockTypeInterface by name
     *
     * @param string $name
     *
     * @return BlockTypeInterface|null
     */
    public function getType(string $name):? BlockTypeInterface;

    /**
     * @param string $name
     * @return null|string
     */
    public function getVoter(string $name):? string;

    /**
     * @param string $name
     * @return null|string
     */
    public function getEntity(string $name):? string;

    /**
     * @param string $name
     * @return null|string
     */
    public function getController(string $name):? string;

    /**
     * create and return BlockEntityInterface instance
     *
     * @param string $name
     * @param array $data
     *
     * @return BlockEntityInterface|null
     */
    public function createEntity(string $name, array $data = []):? BlockEntityInterface;

    /**
     * create and return array
     *
     * @param BlockEntityInterface $entity
     *
     * @return array
     */
    public function createDataFromEntity(BlockEntityInterface $entity): array;


    /**
     * create and return BlockControllerInterface instance
     *
     * @param string $name
     *
     * @return BlockControllerInterface|null
     *
     * @throws ClassNotFoundException
     */
    public function createController(string $name):? BlockControllerInterface;

    /**
     * create and return FormInterface instance
     *
     * @param string $name
     * @param BlockEntityInterface $data
     * @param array $options
     *
     * @return FormInterface|null
     *
     * @throws ClassNotFoundException
     */
    public function createForm(string $name, BlockEntityInterface $data, array $options):? FormInterface;

    /**
     * @return BlockRegistriesInterface
     */
    public function getRegistries(): BlockRegistriesInterface;

    /**
     * @return BlockEntityManagerInterface
     */
    public function getEntityManager(): BlockEntityManagerInterface;
}