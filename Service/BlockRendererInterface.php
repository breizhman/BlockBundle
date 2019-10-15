<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Model\Entity\BlockEntityInterface;

/**
 * Interface BlockRendererInterface
 * @package Cms\BlockBundle\Service
 */
interface BlockRendererInterface
{
    /** render block controller by entity
     *
     * @param BlockEntityInterface $blockEntity
     * @param array $parameters
     * @param string|null $themeName
     *
     * @return mixed
     */
    public function renderBlock(BlockEntityInterface $blockEntity, $parameters = [], string $themeName = null) :? string;

    /**
     * @param string $template
     * @param array $parameters
     * @param string|null $themeName
     *
     * @return null|string
     */
    public function renderTemplate(string $template, $parameters = [], string $themeName = null) :? string;

    /**
     * get theme to display block
     *
     * @param string|null $name
     * @return string
     */
    public function getThemeLayout(string $name = null) : string;
}