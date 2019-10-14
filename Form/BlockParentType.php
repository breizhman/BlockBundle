<?php

namespace BlockBundle\Form;

use Symfony\Component\Form\AbstractType;

/**
 * Class BlockCollectionType
 * @package BlockBundle\Form
 */
class BlockParentType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'block';
    }
}