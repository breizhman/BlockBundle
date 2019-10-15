<?php

namespace Cms\BlockBundle\Model\Entity;

interface BlockEntityInterface
{
    /**
     * get id of the block
     *
     * @return string
     */
    public function getId();

    /**
     * set id of the block
     *
     * @param $id
     * @return BlockEntityInterface
     */
    public function setId($id): BlockEntityInterface;

    /**
     * get name of the block
     *
     * @return string
     */
    public function getName();

    /**
     * set name of the block
     *
     * @param $name
     * @return BlockEntityInterface
     */
    public function setName($name): BlockEntityInterface;
}