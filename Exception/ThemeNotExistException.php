<?php

namespace Cms\BlockBundle\Exception;

class ThemeNotExistException extends \Exception implements ExceptionInterface
{
    public function __construct($name, $theme)
    {
        parent::__construct(sprintf('The theme "%s" with key "%s" not found', $theme, $name));
    }
}
