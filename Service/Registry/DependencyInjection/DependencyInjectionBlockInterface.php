<?php

namespace BlockBundle\Service\Registry\DependencyInjection;

/**
 * Interface DependencyInjectionBlockInterface
 */
interface DependencyInjectionBlockInterface
{
    /**
     * get service by name
     *
     * @param $name
     *
     * @return mixed
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function get($name);

    /**
     * check if service exist
     *
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * @param string $name
     * @return string
     */
    public function findClassName(string $name): string;

    /**
     * @return array
     */
    public function getClassNames(): array;
}