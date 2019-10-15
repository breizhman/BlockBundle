<?php

namespace Cms\BlockBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Cms\BlockBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tb = new TreeBuilder();
        $tb->root('block')
            ->children()
                ->arrayNode('themes')
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;

        return $tb;
    }
}
