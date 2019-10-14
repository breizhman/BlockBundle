<?php

namespace BlockBundle\Model\Controller;

use BlockBundle\Model\Entity\BlockEntityInterface;
use BlockBundle\Model\Type\BlockTypeInterface;
use Symfony\Component\HttpFoundation\Request;

interface BlockControllerInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function renderAction(Request $request): bool;

    /**
     * get the template's path of the block
     *
     * @return string|null
     */
    public function getTemplate() :? string;

    /**
     * get the template's path of the block
     *
     * @param string|null $template
     * @return BlockControllerInterface
     */
    public function setTemplate(?string $template): BlockControllerInterface;

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param array $parameters
     * @return BlockControllerInterface
     */
    public function setParameters(array $parameters): BlockControllerInterface;

    /**
     * @return BlockTypeInterface|null
     */
    public function getBlock(): ?BlockTypeInterface;

    /**
     * @param BlockTypeInterface|null $block
     * @return BlockControllerInterface
     */
    public function setBlock(?BlockTypeInterface $block): BlockControllerInterface;

    /**
     * @return BlockTypeInterface|null
     */
    public function getBlockEntity(): ?BlockEntityInterface;

    /**
     * @param BlockEntityInterface|null $blockEntity
     * @return BlockControllerInterface
     */
    public function setBlockEntity(?BlockEntityInterface $blockEntity): BlockControllerInterface;
}