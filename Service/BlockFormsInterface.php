<?php

namespace BlockBundle\Service;

use BlockBundle\Model\Type\BlockTypeInterface;

interface BlockFormsInterface
{
    /**
     * load all forms from block type
     *
     * @param array $options
     *
     * @return array
     */
    public function load(array $options = []): array;

    /**
     * @param string $blockName
     * @return BlockTypeInterface
     */
    public function findByName(string $blockName):? BlockTypeInterface;
}