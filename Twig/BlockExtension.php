<?php

namespace Cms\BlockBundle\Twig;

use Cms\BlockBundle\Exception\UnexpectedInterfaceException;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Cms\BlockBundle\Service\BlockRendererInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class BlockExtension
 */
class BlockExtension extends AbstractExtension
{
    /**
     * @var BlockRendererInterface
     */
    protected $blockRenderer;

    /**
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     * BlockExtension constructor.
     * @param BlockRendererInterface $blockRenderer
     * @param BlockFactoryInterface $blockFactory
     */
    public function __construct(BlockRendererInterface $blockRenderer, BlockFactoryInterface $blockFactory)
    {
        $this->blockRenderer = $blockRenderer;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('render_block', [$this, 'renderBlock'], ['is_safe' => ['all']]),
            new TwigFunction('render_block_by_name', [$this, 'renderBlockByName'], ['is_safe' => ['all']]),
        ];
    }

    /**
     * @param $blockEntities
     * @param array $parameters
     * @param string|null $themeName
     * @return string
     */
    public function renderBlock($blockEntities, $parameters = [], string $themeName = null)
    {
        $html = '';
        if (!($blockEntities instanceof Collection)) {
            if (!is_array($blockEntities)) {
                $blockEntities = [$blockEntities];
            }
            $blockEntities = new ArrayCollection($blockEntities);
        }

        foreach($blockEntities->toArray() as $blockEntity) {
            if (!empty($blockEntity)) {
                if (!$blockEntity instanceof BlockEntityInterface) {
                    throw new UnexpectedInterfaceException($blockEntity, BlockEntityInterface::class);
                }
                $html .= $this->blockRenderer->renderBlock($blockEntity, $parameters, $themeName);
            }
        }

        return $html;
    }

    /**
     * @param string $blockType
     * @param array $data
     * @param string|null $themeName
     * @return string
     */
    public function renderBlockByType(string $blockType, $data = [], $parameters = [], string $themeName = null)
    {
       return $this->renderBlock($this->blockFactory->createEntity($blockType, $data), $parameters, $themeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'block_twig_extension';
    }
}
