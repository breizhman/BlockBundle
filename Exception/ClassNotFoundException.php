<?php

namespace Cms\BlockBundle\Exception;

class ClassNotFoundException extends \Exception implements ExceptionInterface
{
    public function __construct($className)
    {
        parent::__construct(sprintf('The class "%s" not found ', $className));
    }
}
