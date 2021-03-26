<?php

namespace Cms\BlockBundle\Controller;

use Cms\BlockBundle\Exception\NotFoundException;
use Cms\BlockBundle\Model\Controller\BlockControllerInterface;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactory;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Cms\BlockBundle\Service\BlockRenderer;
use Cms\BlockBundle\Service\BlockRendererInterface;
use Cms\BlockBundle\Service\ConvertCase;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class BlockController
 *
 * @package Cms\BlockBundle\Controller
 */
class BlockController extends AbstractController
{
    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    /**
     * @var BlockRendererInterface
     */
    protected $renderer;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * BlockController constructor.
     *
     * @param BlockFactory  $factory
     * @param BlockRenderer $renderer
     * @param RequestStack  $requestStack
     */
    public function __construct(
        BlockFactory $factory,
        BlockRenderer $renderer,
        RequestStack $requestStack
    )
    {
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->requestStack = $requestStack;
    }

    /**
     * render block view by request
     *
     * @param string $id
     *
     * @return Response
     */
    public function loadView(string $id): Response
    {
        try {
            /** @var BlockEntityInterface $blockEntity */
            $blockEntity = $this->factory->loadEntity($id);

            return new Response($this->renderer->renderBlock($blockEntity));
        } catch (NotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Not found block with id %s ', $id));
        } catch (\Throwable $t) {
            throw new BadRequestHttpException(sprintf('Error on load block with id %d', $id));
        }
    }

    /**
     * launch custom action of block controller
     *
     * @param string $id
     * @param string $action
     *
     * @return Response
     * @throws Exception
     */
    public function action(string $id, string $action): Response
    {
        try {
            /** @var BlockEntityInterface $blockEntity */
            $blockEntity = $this->factory->loadEntity($id);

            /** @var BlockControllerInterface $blockController */
            $blockController = $this->factory->createController($blockEntity->getBlockType());
            if (!$blockController) {
                return new Response();
            }
            if (!$action) {
                return new Response();
            }

            $methodAction = ConvertCase::toCamelCase($action) . 'Action';
            if (!method_exists($blockController, $methodAction)) {
                return new Response();
            }

            $blockController
                ->setParameters(['block' => $blockEntity])
                ->setBlockEntity($blockEntity);

            return $blockController->$methodAction($this->requestStack->getCurrentRequest());

        } catch (NotFoundException $e) {
            throw $this->createNotFoundException(sprintf('Not found block with id %s ', $id));
        } catch (\Throwable $t) {
            throw new BadRequestHttpException(sprintf('Error on action %s for block with id %d: %s', $action, $id, $t->getMessage()));
        }
    }
}