<?php

namespace BlockBundle\Model\Type;

interface BlockTypeInterface
{
    /**
     * get the name identifying the block
     *
     * @return string
     */
    public function getName(): string;

    /**
     * get block groups
     *
     * @return array
     */
    public function getGroups(): array;

    /**
     * get block entity class or service
     * @return string
     */
    public function getEntity():? string;

    /**
     * get block form type class or service
     * @return string
     */
    public function getFormType():? string;

    /**
     * get block controller class or service
     * @return string
     */
    public function getController():? string;

    /**
     * get block voter class or service
     * @return string
     */
    public function getVoter():? string;
}