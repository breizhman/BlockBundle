<?php

namespace Cms\BlockBundle\Entity;

/**
 * Interface BlockIndexationInterface
 * @package Cms\BlockBundle\Entity
 */
interface BlockIndexationInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $id
     * @return BlockIndexationInterface
     */
    public function setId(string $id): BlockIndexationInterface;

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return BlockIndexationInterface
     */
    public function setName(string $name): BlockIndexationInterface;

    /**
     * @return array
     */
    public function getData();

    /**
     * @param array $data
     * @return BlockIndexationInterface
     */
    public function setData(array $data): BlockIndexationInterface;

}
