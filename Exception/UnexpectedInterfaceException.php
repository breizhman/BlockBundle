<?php

namespace Cms\BlockBundle\Exception;

class UnexpectedInterfaceException extends InvalidArgumentException
{
    public function __construct($value, $expectedType)
    {
        parent::__construct(sprintf('Expected class must implement interface "%s", "%s" given', $expectedType, is_object($value) ? get_class($value) : gettype($value)));
    }
}
