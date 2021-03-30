<?php

namespace Cms\BlockBundle\EventListener;

use Cms\BlockBundle\Event\BlockEntityEvent;
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
            BlockEntityEvent::BUILD => 'onBuildEntity',
        ];
    }

    /**
     * @param BlockEntityEvent $event
     */
    public function onBuildEntity(BlockEntityEvent $event): void
    {
        if ($this->entityManager->isNew($event->getBlockEntity())) {
            return;
        }

        $this->entityManager->register($event->getBlockEntity());
    }
}