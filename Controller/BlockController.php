<?php

namespace Cms\BlockBundle\Controller;

use Cms\BlockBundle\Entity\BlockIndexation;
use Cms\BlockBundle\Model\Controller\BlockControllerInterface;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Cms\BlockBundle\Service\BlockRendererInterface;
use Cms\BlockBundle\Service\ConvertCase;
use Cms\BlockBundle\Service\Entity\BlockIndexationManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BlockController
 * @package Cms\BlockBundle\Controller
 */
class BlockController extends AbstractController
{

    /**
     * @var BlockIndexationManagerInterface
     */
    protected $indexationManager;

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
     * render block view by request
     *
     * @param string $name
     * @param string $id
     * @return Response
     *
     * @throws Exception
     */
    public function loadView(string $name, string $id): Response
    {
        /** @var BlockIndexation $blockIndexation */
        $blockIndexation = $this->indexationManager->findByIdAndName($id, $name);
        if (!$blockIndexation) {
            throw $this->createNotFoundException(sprintf('Not found block "%s" with id %s ', $name, $id));
        }

        /** @var BlockEntityInterface $blockEntity */
        $blockEntity = $this->factory->createEntity($blockIndexation->getName(), $blockIndexation->getData());
        if (!$blockEntity) {
            throw new RuntimeException(sprintf('Error on create block entity "%s"', $name), [
                'id' => $id,
                'name' => $name,
                'data' => $blockIndexation->getData()
            ]);
        }

        return new Response($this->renderer->renderBlock($blockEntity));
    }

    /**
     * launch custom action of block controller
     *
     * @param string $name
     * @param string $id
     * @param string $action
     *
     * @return Response
     * @throws Exception
     */
    public function action(string $name, string $id, string $action): Response
    {
        /** @var BlockIndexation $blockIndexation */
        $blockIndexation = $this->indexationManager->findByIdAndName($id, $name);
        if (!$blockIndexation) {
            throw $this->createNotFoundException(sprintf('Not found block "%s" with id %s ', $name, $id));
        }

        /** @var BlockEntityInterface $blockEntity */
        $blockEntity = $this->factory->createEntity($blockIndexation->getName(), $blockIndexation->getData());
        if (!$blockEntity) {
            throw new RuntimeException(sprintf('Error on create block entity "%s"', $name), [
                'id' => $id,
                'name' => $name,
                'data' => $blockIndexation->getData()
            ]);
        }

        /** @var BlockControllerInterface $blockController */
        $blockController = $this->factory->createController($blockEntity->getName());
        if (!$blockController) {
            return new Response();
        }
        if (!$action) {
            return new Response();
        }

        $methodAction = ConvertCase::toCamelCase($action).'Action';
        if (!method_exists($blockController, $methodAction)) {
            return new Response();
        }

        $blockController
            ->setParameters(['block' => $blockEntity])
            ->setBlockEntity($blockEntity)
        ;

        return $blockController->$methodAction($this->requestStack->getCurrentRequest());
    }
}