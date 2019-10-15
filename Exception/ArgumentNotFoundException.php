<?php

namespace Cms\BlockBundle\Exception;

class ArgumentNotFoundException extends \Exception implements ExceptionInterface
{
    public function __construct($argumentExpected, array $data)
    {
        parent::__construct(sprintf('The arguments "%s" not found in data : %s', $argumentExpected, print_r($data, true)));
    }
}
