<?php

namespace Cms\BlockBundle\Exception;

class ThemeNotFoundException extends \Exception implements ExceptionInterface
{
    public function __construct($name)
    {
        parent::__construct(sprintf('The theme with key "%s" not found to configuration', $name));
    }
}
