<?php

namespace Cms\BlockBundle\Service;

use App\EntityManager\CategoryManager;
use Cms\BlockBundle\Exception\ClassNotFoundException;
use Cms\BlockBundle\Exception\ThemeNotExistException;
use Cms\BlockBundle\Exception\ThemeNotFoundException;
use Cms\BlockBundle\Model\Controller\BlockControllerInterface;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Exception\TemplateNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class BlockRenderer
 *
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
     * @var Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $themes;

    private $categoryManager;
    /**
     * BlockRenderer constructor.
     *
     * @param RequestStack          $requestStack
     * @param BlockFactoryInterface $blockFactory
     * @param Environment           $twig
     * @param array                 $themes
     */
    public function __construct(RequestStack $requestStack, BlockFactoryInterface $blockFactory, Environment $twig, array $themes = [], CategoryManager $categoryManager)
    {
        $this->requestStack = $requestStack;
        $this->twig = $twig;
        $this->blockFactory = $blockFactory;
        $this->themes = $themes;
        $this->categoryManager = $categoryManager;
    }

    /**
     * @inheritdoc
     *
     * @param BlockEntityInterface $blockEntity
     * @param array                $parameters
     * @param string|null          $themeName
     *
     * @return string|null
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TemplateNotFoundException
     * @throws ThemeNotExistException
     * @throws ThemeNotFoundException
     * @throws ClassNotFoundException
     */
    public function renderBlock(BlockEntityInterface $blockEntity, $parameters = [], string $themeName = null): ?string
    {
        /** @var BlockControllerInterface $blockController */
        $blockController = $this->blockFactory->createController($blockEntity->getBlockType());
        if (!$blockController) {
            return null;
        }

        $parameters['block'] = $blockEntity;
        $parameters['theme'] = $themeName;

        $blockController
            ->setParameters($parameters)
            ->setBlockEntity($blockEntity);

        if (!$blockController->renderAction($this->requestStack->getCurrentRequest())) {
            return null;
        }

        return $this->renderTemplate(
            $blockController->getTemplate(),
            array_replace_recursive($parameters, $blockController->getParameters()),
            $themeName
        );
    }

    /**
     * @inheritdoc
     *
     * @param string      $template
     * @param array       $parameters
     * @param string|null $themeName
     *
     * @return string|null
     *
     * @throws TemplateNotFoundException
     * @throws ThemeNotExistException
     * @throws ThemeNotFoundException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderTemplate(string $template, $parameters = [], string $themeName = null): ?string
    {
        if (!$this->twig->getLoader()->exists($template)) {
            throw new TemplateNotFoundException($template);
        }

        return $this->twig->render($template, array_replace_recursive([
            'theme_layout' => $this->getThemeLayout($parameters['theme'] ?? $themeName),
        ], $parameters));
    }

    /**
     * @inheritdoc
     *
     * @throws ThemeNotExistException
     * @throws ThemeNotFoundException
     */
    public function getThemeLayout(string $name = null): string
    {
        if (!isset($this->themes[$name])) {
            $name = 'default';
        }

        if (!isset($this->themes[$name])) {
            throw new ThemeNotFoundException($name);
        }

        $theme = $this->themes[$name];
        if (!$this->twig->getLoader()->exists($theme)) {
            throw new ThemeNotExistException($name, $theme);
        }

        return $theme;
    }
}