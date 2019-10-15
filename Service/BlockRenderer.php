<?php

namespace Cms\BlockBundle\Service;

use Cms\BlockBundle\Exception\ThemeNotExistException;
use Cms\BlockBundle\Exception\ThemeNotFoundException;
use Cms\BlockBundle\Model\Controller\BlockControllerInterface;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Exception\TemplateNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class BlockRenderer
 * @package Cms\BlockBundle\Service
 */
class BlockRenderer implements BlockRendererInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var BlockFactoryInterface
     */
    private $blockFactory;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $themes;

    /**
     * BlockRenderer constructor.
     *
     * @param RequestStack $requestStack
     * @param BlockFactoryInterface $blockFactory
     * @param EngineInterface $templating
     * @param array $themes
     */
    public function __construct(RequestStack $requestStack, BlockFactoryInterface $blockFactory, EngineInterface $templating, array $themes = [])
    {
        $this->requestStack = $requestStack;
        $this->templating = $templating;
        $this->blockFactory = $blockFactory;
        $this->themes = $themes;
    }

    /**
     * @inheritdoc
     *
     * @throws TemplateNotFoundException
     * @throws ThemeNotExistException
     * @throws ThemeNotFoundException
     * @throws \Cms\BlockBundle\Exception\ClassNotFoundException
     */
    public function renderBlock(BlockEntityInterface $blockEntity, $parameters = [], string $themeName = null) :? string
    {

        /** @var BlockControllerInterface $blockController */
        $blockController = $this->blockFactory->createController($blockEntity->getName());
        if (!$blockController) {
            return null;
        }

        $parameters['block'] = $blockEntity;
        $parameters['theme'] = $themeName;

        $blockController
            ->setParameters($parameters)
            ->setBlockEntity($blockEntity)
        ;

        if ($blockController->renderAction($this->requestStack->getCurrentRequest())) {
            return $this->renderTemplate($blockController->getTemplate(),
                array_replace_recursive($parameters, $blockController->getParameters())
            , $themeName);
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @throws TemplateNotFoundException
     * @throws ThemeNotExistException
     * @throws ThemeNotFoundException
     */
    public function renderTemplate(string $template, $parameters = [], string $themeName = null) :? string
    {
        if (!$this->templating->exists($template)) {
            throw new TemplateNotFoundException($template);
        }

        return $this->templating->render($template, array_replace_recursive([
            'theme_layout' => $this->getThemeLayout($parameters['theme'] ?? $themeName)
        ], $parameters));
    }

    /**
     * @inheritdoc
     *
     * @throws ThemeNotExistException
     * @throws ThemeNotFoundException
     */
    public function getThemeLayout(string $name = null) : string
    {
        if (!isset($this->themes[$name])) {
            $name = 'default';
        }

        if (!isset($this->themes[$name])) {
            throw new ThemeNotFoundException($name);
        }

        $theme = $this->themes[$name];
        if (!$this->templating->exists($theme)) {
            throw new ThemeNotExistException($name, $theme);
        }

        return $theme;
    }
}