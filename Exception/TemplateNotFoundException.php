<?php

namespace BlockBundle\Exception;

class TemplateNotFoundException extends \Exception implements ExceptionInterface
{
    public function __construct($className)
    {
        parent::__construct(sprintf('The template "%s" not found ', $className));
    }
}
