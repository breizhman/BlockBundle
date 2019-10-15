<?php

namespace Cms\BlockBundle\Model\Form;

use Cms\BlockBundle\Model\Type\BlockTypeInterface;

interface BlockFormTypeInterface
{
    /**
     * @return BlockTypeInterface|null
     */
    public function getBlock(): ?BlockTypeInterface;

    /**
     * @param BlockTypeInterface|null $block
     * @return BlockFormTypeInterface
     */
    public function setBlock(?BlockTypeInterface $block): BlockFormTypeInterface;

    /**
     * @return string
     */
    public function getDataClass():? string;

    /**
     * @param string $dataClass
     * @return BlockFormTypeInterface
     */
    public function setDataClass(string $dataClass = null): BlockFormTypeInterface;
}