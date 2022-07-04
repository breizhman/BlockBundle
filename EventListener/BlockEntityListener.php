<?php

namespace Cms\BlockBundle\EventListener;

use Cms\BlockBundle\Event\PostBuildBlockEvent;
use Cms\BlockBundle\Event\PreBuildBlockEvent;
use Cms\BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BlockEntityListener
 *
 * @package Cms\BlockBundle\EventListener
 */
class BlockEntityListener implements EventSubscriberInterface
{
    /**
     * @var BlockEntityManagerInterface
     */
    private $entityManager;

    /**
     * BlockEntityListener constructor.
     *
     * @param BlockEntityManagerInterface $entityManager
     */
    public function __construct(BlockEntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PreBuildBlockEvent::PRE_BUILD  => 'onPreBuildEntity',
            PostBuildBlockEvent::POST_BUILD => 'onPostBuildEntity',
        ];
    }

    /**
     * @param PostBuildBlockEvent $event
     */
    public function onPreBuildEntity(PreBuildBlockEvent $event): void
    {
        $blockId = $event->getBlockData()['blockId'] ?? null;
        $parentBlockId = $event->getBlockData()['parentBlockId'] ?? null;

        if (!($blockId && $parentBlockId)) {
            return;
        }

        if ($this->entityManager->isLoading($parentBlockId)) {
            $this->entityManager->flagAsLoading($blockId);
        }
    }

    /**
     * @param PostBuildBlockEvent $event
     */
    public function onPostBuildEntity(PostBuildBlockEvent $event): void
    {
        if ($this->entityManager->isNew($event->getBlockEntity())) {
            return;
        }

        $this->entityManager->register($event->getBlockEntity());
    }
}