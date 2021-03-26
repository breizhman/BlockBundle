<?php

namespace Cms\BlockBundle\Model\Entity;

/**
 * Class AbstractEntity
 *
 * @package Cms\BlockBundle\Model\Entity
 */
abstract class AbstractEntity implements BlockEntityInterface
{
    /**
     * @var string
     */
    protected $blockId;

    /**
     * @var string
     */
    protected $blockType;

    /**
     * on clone, clone sub objects
     */
    public function __clone() {
        foreach($this as $key => $val) {
            if (is_object($val)) {
                $this->{$key} = clone $val;
            } else if (is_array($val)) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

    /**
     * @return string
     */
    public function getBlockId()
    {
        return $this->blockId;
    }

    /**
     * @param string $blockId
     *
     * @return BlockEntityInterface
     */
    public function setBlockId($id): BlockEntityInterface
    {
        $this->blockId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockType()
    {
        return $this->blockType;
    }

    /**
     * @param string $type
     *
     * @return BlockEntityInterface
     */
    public function setBlockType($type): BlockEntityInterface
    {
        $this->blockType = $type;
        return $this;
    }
}