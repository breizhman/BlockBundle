<?php

namespace Cms\BlockBundle\Form;

use Symfony\Component\Form\AbstractType;

/**
 * Class BlockCollectionType
 * @package Cms\BlockBundle\Form
 */
class BlockParentType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'block';
    }
}