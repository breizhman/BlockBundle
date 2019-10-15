<?php

namespace Cms\BlockBundle;

use Cms\BlockBundle\DependencyInjection\BlockDataTransformersPass;
use Cms\BlockBundle\DependencyInjection\BlockPass;
use Cms\BlockBundle\DependencyInjection\BlockRegistriesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BlockBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new BlockPass());
        $container->addCompilerPass(new BlockDataTransformersPass());
        $container->addCompilerPass(new BlockRegistriesPass());
    }
}