<?php

namespace Cms\BlockBundle\Model\Entity;

use Cms\BlockBundle\Collection\BlockCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class AbstractEntity
 *
 * @package Cms\BlockBundle\Model\Entity
 */
abstract class AbstractEntity implements BlockEntityInterface
{
    use CloneSubObjectsTrait;

    /**
     * @var string
     */
    protected $blockId;

    /**
     * @var string
     */
    protected $blockType;

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