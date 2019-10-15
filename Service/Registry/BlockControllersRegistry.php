<?php

namespace Cms\BlockBundle\Service\Registry;

use Cms\BlockBundle\Model\Controller\BlockControllerInterface;

/**
 * Class BlockControllersRegistry
 * @package Cms\BlockBundle\Service\Registry
 */
class BlockControllersRegistry extends AbstractBlockRegistry
{
    /**
     * {@inheritdoc}
     */
    protected function getInterfaceClassName(): string
    {
        return BlockControllerInterface::class;
    }
}