<?php

namespace BlockBundle\Service\Registry;

use BlockBundle\Model\Controller\BlockControllerInterface;

/**
 * Class BlockControllersRegistry
 * @package BlockBundle\Service\Registry
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