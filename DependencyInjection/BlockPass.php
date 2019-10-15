<?php

namespace Cms\BlockBundle\DependencyInjection;


use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Service\ResolvedBlockType;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class BlockPass
 * @package BlockBundle\DependencyInjection
 */
class BlockPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * @var array
     */
    private $aliasMapByType = [];

    /**
     * @var array
     */
    private $servicesMapByType = [];

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->container = $container;

        $this->processDependencyInjection('block.dependency_injection.types', 'block.type');
        $this->processDependencyInjection('block.dependency_injection.controllers', 'block.controller');
    }

    /**
     * @param string $dependencyInjectionTag
     * @param string $serviceTag
     *
     * @throws\Cms\BlockBundle\Exception\ClassNotFoundException
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function processDependencyInjection(string $dependencyInjectionTag,  string $serviceTag)
    {
        if (!$this->container->hasDefinition($dependencyInjectionTag)) {
            return;
        }

        switch ($dependencyInjectionTag) {
            case 'block.dependency_injection.types': $this->processDependencyInjectionTypes($dependencyInjectionTag, $serviceTag);
                break;
        }

        $this->setDependencyInjection($dependencyInjectionTag, $serviceTag);
    }

    /**
     * @param string $dependencyInjectionTag
     * @param string $serviceTag
     */
    private function setDependencyInjection(string $dependencyInjectionTag, string $serviceTag)
    {
        $definition = $this->container->getDefinition($dependencyInjectionTag);
        $definition->replaceArgument(0, ServiceLocatorTagPass::register($this->container, $this->servicesMapByType[$serviceTag] ?? []));
        $definition->replaceArgument(1, $this->aliasMapByType[$serviceTag] ?? []);
    }


    /**
     * @param string $serviceTag
     *
     * @throws\Cms\BlockBundle\Exception\ClassNotFoundException
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function processDependencyInjectionTypes(string $dependencyInjectionTag, string $serviceTag)
    {

        if (!isset($this->aliasMapByType[$serviceTag])) {
            $this->aliasMapByType[$serviceTag] = [];
        }

        if (!isset($this->servicesMapByType['block.type'])) {
            $this->servicesMapByType[$serviceTag] = [];
        }

        $blockRegistry = $this->container->get('block.resolved_type.factory');

        // Builds an array with fully-qualified type class names as keys and service IDs as values
        foreach ($this->container->findTaggedServiceIds($serviceTag, true) as $serviceId => $tag) {
            // Add form type service to the service locator
            $serviceDefinition = $this->container->getDefinition($serviceId);
            $className = $serviceDefinition->getClass();

            $refectionClass = new \ReflectionClass($className);
            if ($refectionClass->implementsInterface(BlockTypeInterface::class)) {
                /** @var ResolvedBlockType $blockType */
                $blockType = $blockRegistry->createResolvedBlock(new $className);

                // set info block form
                $this->prepareBlockClass($blockType, 'block.form_type', $blockType->getFormType(),
                    function($className) use ($serviceId, $blockType) {
                        $this->container->getDefinition($className)
                            ->addMethodCall('setBlock', [new Reference($serviceId)])
                            ->addMethodCall('setDataClass', [$blockType->getEntity()])
                        ;
                    }
                );

                // set info block view
                $this->prepareBlockClass($blockType, 'block.controller', $blockType->getController(),
                    function($className) use ($serviceId) {
                        $this->container->getDefinition($className)
                            ->addMethodCall('setBlock', [new Reference($serviceId)])
                        ;
                    }
                );

                // set info block voter
                $this->prepareBlockClass($blockType, 'block.voter', $blockType->getVoter(),
                    function($className) use ($serviceId) {
                        $this->container->getDefinition($className)
                            ->addMethodCall('setBlock', [new Reference($serviceId)])
                        ;
                    }
                );

                $this->aliasMapByType['block.type'][$blockType->getName()] = $className;
                $this->servicesMapByType['block.type'][$className] = new Reference($serviceId);
            }
        }
    }

    /**
     * @param BlockTypeInterface $blockType
     * @param string $serviceTag
     * @param string $className
     * @param $callback
     */
    public function prepareBlockClass(BlockTypeInterface $blockType, string $serviceTag, string $className, $callback)
    {
        if ($this->container->hasDefinition($className)) {

            $callback($className);

            $this->aliasMapByType[$serviceTag][$blockType->getName()] = $className;
            $this->servicesMapByType[$serviceTag][$className] = new Reference($className);
        }
    }
}
