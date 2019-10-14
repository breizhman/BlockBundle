<?php

namespace BlockBundle\Model\Controller;

use BlockBundle\Model\Controller\BlockControllerInterface;
use BlockBundle\Model\Entity\BlockEntityInterface;
use BlockBundle\Model\Type\BlockTypeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractController
 * @package BlockBundle\Model\Controller
 */
abstract class AbstractController implements BlockControllerInterface
{
    /**
     * @var BlockTypeInterface|null
     */
    protected $block = null;

    /**
     * @var BlockEntityInterface|null
     */
    protected $blockEntity = null;

    /**
     * @var null|string
     */
    protected $template = null;

    /**
     * @var array params use to template
     */
    protected $parameters = [];

    /**
     * @inheritdoc
     */
    public function renderAction(Request $request): bool
    {
        return true;
    }

    /**
     * set template and params to prepare render action
     *
     * @param null|string $template
     * @param array $parameters
     *
     * @return bool
     */
    public function prepareRender(?string $template, array $parameters = []): bool
    {
        $this
            ->setTemplate($template)
            ->setParameters($parameters)
        ;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate() :? string
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate(?string $template): BlockControllerInterface
    {
        $this->template = $template;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters): BlockControllerInterface
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @return BlockTypeInterface|null
     */
    public function getBlock(): ?BlockTypeInterface
    {
        return $this->block;
    }

    /**
     * @param BlockTypeInterface|null $block
     * @return BlockControllerInterface
     */
    public function setBlock(?BlockTypeInterface $block): BlockControllerInterface
    {
        $this->block = $block;
        return $this;
    }

    /**
     * @return BlockEntityInterface|null
     */
    public function getBlockEntity(): ?BlockEntityInterface
    {
        return $this->blockEntity;
    }

    /**
     * @param BlockEntityInterface|null $blockEntity
     * @return BlockControllerInterface
     */
    public function setBlockEntity(?BlockEntityInterface $blockEntity): BlockControllerInterface
    {
        $this->blockEntity = $blockEntity;
        return $this;
    }
}