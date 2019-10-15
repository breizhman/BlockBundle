<?php

namespace Cms\BlockBundle\Controller;

use Cms\BlockBundle\Entity\BlockIndexation;
use Cms\BlockBundle\Model\Controller\BlockControllerInterface;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\ConvertCase;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BlockController
 * @package Cms\BlockBundle\Controller
 */
class BlockController extends Controller
{
    /**
     * render block view by request
     *
     * @param Request $request
     * @param string $name
     * @param string $id
     * @return Response
     *
     * @throws \Exception
     * @throws \Cms\BlockBundle\Exception\TemplateNotFoundException
     * @throws \Cms\BlockBundle\Exception\ThemeNotExistException
     * @throws \Cms\BlockBundle\Exception\ThemeNotFoundException
     */
    public function renderAction(Request $request, string $name, string $id): Response
    {
        /** @var BlockIndexation $blockIndexation */
        $blockIndexation = $this->get('block.entity_manager.indexation')->findByIdAndName($id, $name);
        if (!$blockIndexation) {
            throw $this->createNotFoundException(sprintf('Not found block "%s" with id %s ', $name, $id));
        }

        /** @var BlockEntityInterface $blockEntity */
        $blockEntity = $this->get('block.factory')->createEntity($blockIndexation->getName(), $blockIndexation->getData());
        if (!$blockEntity) {
            throw new \Exception(sprintf('Error on create block entity "%s"', $name), [
                'id' => $id,
                'name' => $name,
                'data' => $blockIndexation->getData()
            ]);
        }

        return new Response($this->get('block.renderer')->renderBlock($blockEntity));
    }

    /**
     * launch custom action of block controller
     *
     * @param string $name
     * @param string $id
     * @param string $action
     *
     * @return Response
     * @throws \Cms\BlockBundle\Exception\ClassNotFoundException
     */
    public function customAction(string $name, string $id, string $action): Response
    {
        /** @var BlockIndexation $blockIndexation */
        $blockIndexation = $this->get('block.entity_manager.indexation')->findByIdAndName($id, $name);
        if (!$blockIndexation) {
            throw $this->createNotFoundException(sprintf('Not found block "%s" with id %s ', $name, $id));
        }

        /** @var BlockEntityInterface $blockEntity */
        $blockEntity = $this->get('block.factory')->createEntity($blockIndexation->getName(), $blockIndexation->getData());
        if (!$blockEntity) {
            throw new \Exception(sprintf('Error on create block entity "%s"', $name), [
                'id' => $id,
                'name' => $name,
                'data' => $blockIndexation->getData()
            ]);
        }

        /** @var BlockControllerInterface $blockController */
        $blockController = $this->get('block.factory')->createController($blockEntity->getName());
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

        return call_user_func_array([$blockController, $methodAction], [$this->get('request_stack')->getCurrentRequest()]);
    }
}