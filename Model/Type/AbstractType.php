<?php

namespace Cms\BlockBundle\Model\Type;

abstract class AbstractType implements BlockTypeInterface
{
    /**
     * @inheritdoc
     */
    public function getGroups(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getEntity():? string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getFormType():? string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getController():? string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getVoter():? string
    {
        return null;
    }
}