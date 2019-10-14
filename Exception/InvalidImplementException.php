<?php

namespace BlockBundle\Exception;

class InvalidImplementException extends \Exception implements ExceptionInterface
{
    public function __construct($className, $implementClassName)
    {
        parent::__construct(sprintf('The object "%s" not implement "%s"', $className, $implementClassName));
    }
}
