<?php

namespace BlockBundle;

use BlockBundle\DependencyInjection\BlockDataTransformersPass;
use BlockBundle\DependencyInjection\BlockPass;
use BlockBundle\DependencyInjection\BlockRegistriesPass;
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