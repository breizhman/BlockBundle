<?php

namespace Cms\BlockBundle\EventListener;

use Cms\BlockBundle\Collection\BlockCollection;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Cms\BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\EventDispatcher\Event;
use Cms\BlockBundle\Annotation as BlockAnnotation;

/**
 * Class BlockListener
 *
 * @package Cms\BlockBundle\EventListener
 */
class BlockListener extends Event
{
    /**
     * @var BlockFactoryInterface
     */
    private $blockFactory;

    /**
     * @var BlockEntityManagerInterface
     */
    private $blockEntityManager;

    /**
     * @var array
     */
    private $entitiesWithBlocks = [];

    /**
     * @param BlockFactoryInterface       $blockFactory
     * @param BlockEntityManagerInterface $blockEntityManager
     */
    public function __construct(BlockFactoryInterface $blockFactory, BlockEntityManagerInterface $blockEntityManager)
    {
        $this->blockFactory = $blockFactory;
        $this->blockEntityManager = $blockEntityManager;
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function postLoad(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        $blockProperties = $this->getBlockProperties($entity);
        if (!$blockProperties) {
            return;
        }

        $this->addEntityWithBlocks($entity, $blockProperties);
    }

    /**
     * @param LifecycleEventArgs $event
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function postPersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($this->isAlreadyRegister($entity)) {
            return;
        }

        $blockProperties = $this->getBlockProperties($entity);
        if (!$blockProperties) {
            return;
        }

        $this->addEntityWithBlocks($entity, $blockProperties);
    }

    /**
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event): void
    {
        foreach ($this->entitiesWithBlocks as $oid => $data) {

            if (!isset($data['entity'], $data['block_properties'])) {
                continue;
            }

            $entity = $data['entity'];
            $blockProperties = $data['block_properties'];

            $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) {

                foreach ($blockEntities as $blockEntity) {

                    if (!$blockEntity instanceof BlockEntityInterface) {
                        continue;
                    }

                    if (!$this->blockEntityManager->hasChanged($blockEntity)) {
                        continue;
                    }

                    $this->blockEntityManager->persist($blockEntity);
                }
            });
        }
    }

    /**
     * @param OnFlushEventArgs $event
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function onFlush(OnFlushEventArgs $event): void
    {
        /* @var EntityManagerInterface $em */
        $em = $event->getEntityManager();
        /* @var $uow UnitOfWork */
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $oid => $entity) {
            $this->persistBlockEntities($uow, $entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $oid => $entity) {
            $this->updateBlockEntities($uow, $entity);
        }

        foreach ($uow->getScheduledEntityDeletions() as $oid => $entity) {
            $this->deleteBlockEntities($uow, $entity);
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event): void
    {
        $this->blockEntityManager->flush();
    }

    /**
     * insert block data from entity
     *
     * @param UnitOfWork $uow
     * @param mixed      $entity
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function persistBlockEntities(UnitOfWork $uow, $entity): void
    {
        $blockProperties = $this->getBlockProperties($entity);
        if (!$blockProperties) {
            return;
        }

        $this->addEntityWithBlocks($entity, $blockProperties);

        $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) {
            /** @var BlockEntityInterface $blockEntity */
            foreach ($blockEntities as $blockEntity) {
                if (!$blockEntity instanceof BlockEntityInterface) {
                    continue;
                }

                $this->blockEntityManager->persist($blockEntity);
            }
        });
    }

    /**
     * update block data from entity
     *
     * @param UnitOfWork $uow
     * @param mixed      $entity
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function updateBlockEntities(UnitOfWork $uow, $entity): void
    {
        // get properties data from load entity
        $blockProperties = $this->getBlockProperties($entity);
        if (!$blockProperties) {
            return;
        }

        $this->updateEntityWithBlocks($entity);

        $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) {

            foreach ($blockEntities as $blockEntity) {
                if (!$blockEntity instanceof BlockEntityInterface) {
                    continue;
                }

                $this->blockEntityManager->persist($blockEntity);
            }
        });

    }

    /**
     * delete block data from entity
     *
     * @param UnitOfWork $uow
     * @param mixed      $entity
     */
    public function deleteBlockEntities(UnitOfWork $uow, $entity): void
    {
        $blockProperties = $this->getBlockProperties($entity);
        if (!$blockProperties) {
            return;
        }

        $this->deleteEntityWithBlocks($entity);

        $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) {

            /** @var BlockEntityInterface $blockEntity */
            foreach ($blockEntities as $blockEntity) {
                if (!$blockEntity instanceof BlockEntityInterface) {
                    continue;
                }
                $this->blockEntityManager->remove($blockEntity);
            }
        });

    }

    /**
     * @param object   $entity
     * @param array    $blockProperties
     * @param \Closure $callFunc
     *
     * @return BlockListener
     */
    protected function runBlockProperties(object $entity, array $blockProperties, \Closure $callFunc): BlockListener
    {
        foreach ($blockProperties as $property) {
            if (!$property['annotation']) {
                continue;
            }

            $value = $this->blockEntityManager->getProperty()->getValue($entity, $property['name']);

            $blockEntities = [];
            if ($value instanceof BlockEntityInterface) {
                $blockEntities = [$value];
            } elseif ($value instanceof BlockCollection) {
                $blockEntities = $value->toArray();
            }

            $callFunc($entity, $property, $blockEntities, []);
        }

        return $this;
    }

    /**
     * @param $entity
     *
     * @return null|array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getBlockProperties($entity): ?array
    {
        $oid = spl_object_hash($entity);
        if (isset($this->entitiesWithBlocks[$oid]['property'])) {
            return $this->entitiesWithBlocks[$oid]['property'];
        }

        $reflectionClass = new \ReflectionClass(get_class($entity));
        if (!$reflectionClass) {
            return null;
        }
        // Prepare doctrine annotation reader
        $reader = new AnnotationReader();

        $data = [];
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $annotations = $reader->getPropertyAnnotations($reflectionProperty);
            if (!$annotations) {
                continue;
            }

            $blockAnnotation = null;
            foreach ($annotations as $annotation) {
                if ($annotation instanceof BlockAnnotation\Type || $annotation instanceof BlockAnnotation\Collection) {
                    $blockAnnotation = $annotation;
                    break;
                }
            }

            if (!$blockAnnotation) {
                continue;
            }

            $data[$propertyName] = [
                'annotation' => $blockAnnotation,
                'name'       => $propertyName,
            ];

        }

        return $data;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isAlreadyRegister(object $entity): bool
    {
        return isset($this->entitiesWithBlocks[spl_object_hash($entity)]);
    }

    /**
     * @param object $entity
     * @param array  $blockProperties
     */
    public function addEntityWithBlocks(object $entity, array $blockProperties): void
    {
        $this->entitiesWithBlocks[spl_object_hash($entity)] = [
            'entity'           => $entity,
            'block_properties' => $blockProperties,
        ];
    }

    /**
     * @param object $entity
     */
    public function updateEntityWithBlocks(object $entity): void
    {
        $this->entitiesWithBlocks[spl_object_hash($entity)]['entity'] = $entity;
    }

    /**
     * @param object $entity
     */
    public function deleteEntityWithBlocks(object $entity): void
    {
        unset($this->entitiesWithBlocks[spl_object_hash($entity)]);
    }
}
