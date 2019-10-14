<?php

namespace BlockBundle\EventListener;

use BlockBundle\Model\Entity\BlockEntityInterface;
use BlockBundle\Service\BlockFactoryInterface;
use BlockBundle\Service\Entity\BlockEntityManagerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\EventDispatcher\Event;
use BlockBundle\Annotation as BlockAnnotation;

/**
 * Class BlockListener
 * @package BlockBundle\EventListener
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
    private $originEntities = [];

    /**
     * @var array
     */
    private $blockEntitiesToFlush = [];

    /**
     * @var bool
     */
    private $isFlushing = false;

    /**
     * @param BlockFactoryInterface $blockFactory
     * @param BlockEntityManagerInterface $blockEntityManager
     */
    public function __construct(BlockFactoryInterface $blockFactory, BlockEntityManagerInterface $blockEntityManager)
    {
        $this->blockFactory = $blockFactory;
        $this->blockEntityManager = $blockEntityManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $this->loadBlockToEntity($args);
    }

    /**
     * @param OnFlushEventArgs $event
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        if (!$this->isFlushing) {
            /* @var $em \Doctrine\ORM\EntityManager */
            $em = $event->getEntityManager();
            /* @var $uow UnitOfWork */
            $uow = $em->getUnitOfWork();

            foreach ($uow->getScheduledEntityInsertions() as $entity) {
                $this->persistBlockEntities($uow, $entity);
            }

            foreach ($uow->getScheduledEntityUpdates() as $entity) {
                $this->updateBlockEntities($uow, $entity);
            }

            foreach ($uow->getScheduledEntityDeletions() as $entity) {
                $this->deleteBlockEntities($uow, $entity);
            }
        }
    }

    /**
     * @param PostFlushEventArgs $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $event->getEntityManager();

        if (!$this->isFlushing) {
            foreach ($this->blockEntitiesToFlush as $data) {
                foreach ($data['properties'] as $property) {
                    $blocks = [];
                    foreach ($property['blocks'] as $blockEntity) {
                        $blocks[] = $this->blockFactory->createDataFromEntity($blockEntity);

                        $this->blockEntityManager->persistIndexation($blockEntity);
                    }

                    // case only one entity
                    if ($blocks && $property['annotation'] instanceof BlockAnnotation\Type) {
                        $blocks = current($blocks);
                    }

                    $blocks = !empty($blocks) ? $blocks : null;
                    $this->blockEntityManager->getProperty()->setValue($data['entity'], $property['name'], $blocks);
                }

                $em->persist($data['entity']);
            }

            $this->isFlushing = true;

            $em->flush();

            $this->isFlushing = false;
        }
    }

    /**
     * keep block entity to save data on post flush
     *
     * @param object $entity
     * @param array $property
     * @param BlockEntityInterface $blockEntity
     */
    public function addBlockEntityForCreateData($entity, array $property, BlockEntityInterface $blockEntity)
    {
        $key = spl_object_hash($entity);
        if (!isset($this->blockEntitiesToFlush[$key])) {
            $this->blockEntitiesToFlush[$key] = [
                'entity' => $entity,
            ];
        }

        if (!isset($this->blockEntitiesToFlush[$key]['properties'][$property['name']])) {
            $this->blockEntitiesToFlush[$key]['properties'][$property['name']] = $property;
        }

        $blockKey = spl_object_hash($blockEntity);
        $this->blockEntitiesToFlush[$key]['properties'][$property['name']]['blocks'][$blockKey] = $blockEntity;
    }

    /**
     * load block to current entity
     *
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function loadBlockToEntity(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $blockProperties = $this->getBlockProperties($entity);

        if ($blockProperties) {
            // save properties info for current entity
            $this->setOriginBlockProperties($entity, $blockProperties);

            $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) {

                $value = $originValue = null;
                $annotation = $property['annotation'];

                if ($blockEntities) {

                    // case only one block entity
                    if ($annotation  instanceof BlockAnnotation\Type) {
                        $blockEntities['name'] = $annotation->name;
                        $value = $originValue = $this->blockFactory->createEntity($annotation->name, $blockEntities);

                    // case many block entity (block collection)
                    } else {
                        $value = $originValue = [];
                        foreach ($blockEntities as $data) {
                            if ($data && isset($data['name']) && (empty($annotation->names) || in_array($data['name'], (array) $annotation->names))) {
                                $blockEntity = $this->blockFactory->createEntity($data['name'], $data);
                                if ($blockEntity) {
                                    $value[] = $blockEntity;
                                    $originValue[$blockEntity->getId()] = clone $blockEntity;
                                }
                            }
                        }
                    }
                }

                // add block entity to current entity
                $this->blockEntityManager->getProperty()->setValue($entity, $property['name'], $value);

                // save origin property value. Use when persist or remove current entity
                $originData = $this->getOriginBlockProperties($entity);
                $originData[$property['name']]['value'] = $originValue;
                $this->setOriginBlockProperties($entity, $originData);
            });
        }
    }

    /**
     * insert block data from entity
     *
     * @param UnitOfWork $uow
     * @param mixed $entity
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function persistBlockEntities(UnitOfWork $uow, $entity)
    {
        $blockProperties = $this->getBlockProperties($entity);
        if ($blockProperties) {
            $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) use ($uow) {
                /** @var BlockEntityInterface $blockEntity */
                foreach ($blockEntities as $blockEntity) {
                    if ($blockEntity && $blockEntity instanceof BlockEntityInterface) {
                        $this->blockEntityManager->persist($blockEntity, false);
                        $this->addBlockEntityForCreateData($entity, $property, $blockEntity);
                    }
                }
            });
        }
    }

    /**
     * update block data from entity
     *
     * @param UnitOfWork $uow
     * @param mixed $entity
     */
    public function updateBlockEntities( UnitOfWork $uow, $entity)
    {
        // get properties data from load entity
        $blockProperties = $this->getOriginBlockProperties($entity);

        if ($blockProperties) {
            $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) use ($uow) {

                /** @var BlockEntityInterface $blockEntity */
                foreach ($blockEntities as $blockEntity) {
                    if ($blockEntity && $blockEntity instanceof BlockEntityInterface) {
                        $this->blockEntityManager->persist($blockEntity, false);

                        $this->addBlockEntityForCreateData($entity, $property, $blockEntity);

                        // case block entity update, no need to delete it
                        unset($entitiesToDelete[$blockEntity->getId()]);
                    }
                }

                if (!empty($entitiesToDelete)) {
                    /** @var BlockEntityInterface $blockEntity */
                    foreach ($entitiesToDelete as $blockEntity) {
                        $this->blockEntityManager->remove($blockEntity);
                    }
                }
            });
        }
    }

    /**
     * delete block data from entity
     *
     * @param UnitOfWork $uow
     * @param mixed $entity
     */
    public function deleteBlockEntities(UnitOfWork $uow, $entity)
    {
        $blockProperties = $this->getOriginBlockProperties($entity);

        if ($blockProperties) {
            $this->runBlockProperties($entity, $blockProperties, function ($entity, $property, $blockEntities, $entitiesToDelete) use ($uow) {
                if (!empty($entitiesToDelete)) {
                    /** @var BlockEntityInterface $blockEntity */
                    foreach ($entitiesToDelete as $blockEntity) {
                        if ($blockEntity && $blockEntity instanceof BlockEntityInterface) {
                            $this->blockEntityManager->remove($blockEntity);
                        }
                    }
                }
            });
        }
    }

    /**
     * @param object $entity
     * @param array $blockProperties
     * @param \Closure $callFunc
     * @return BlockListener
     */
    protected function runBlockProperties(object $entity, array $blockProperties, \Closure $callFunc): BlockListener
    {
        foreach ($blockProperties as $property) {
            if ($property['annotation']) {

                $blockEntities = $this->blockEntityManager->getProperty()->getValue($entity, $property['name']);

                if (!is_array($blockEntities)) {
                    $blockEntities = $blockEntities ? [$blockEntities] : [];
                }

                // all origin block entity must be to delete
                $entitiesToDelete = $property['value'] ?? [];
                if ($entitiesToDelete instanceof BlockEntityInterface) {
                    $entitiesToDelete = [$entitiesToDelete->getId() => $entitiesToDelete];
                }

                call_user_func_array($callFunc, [$entity, $property, $blockEntities, $entitiesToDelete]);
            }
        }

        return $this;
    }
    /**
     * @param $entity
     * @return null|array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getBlockProperties($entity):? array
    {
        $reflectionClass = new \ReflectionClass(get_class($entity));
        if ($reflectionClass) {

            // Prepare doctrine annotation reader
            $reader = new AnnotationReader();

            $data = [];
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $propertyName = $reflectionProperty->getName();
                $annotations = $reader->getPropertyAnnotations($reflectionProperty);
                if ($annotations) {
                    $blockAnnotation = null;
                    foreach ($annotations as $annotation) {
                        if ($annotation instanceof BlockAnnotation\Type || $annotation instanceof BlockAnnotation\Collection) {
                            $blockAnnotation = $annotation;
                            break;
                        }
                    }

                    if ($blockAnnotation) {
                        $data[$propertyName] = [
                            'annotation' => $blockAnnotation,
                            'name' => $propertyName,
                        ];
                    }
                }
            }

            return $data;
        }

        return null;
    }

    /**
     * @param object $entity
     * @return array|null
     */
    public function getOriginBlockProperties(object $entity):? array
    {
        $oid = spl_object_hash($entity);
        return $this->originEntities[$oid] ?? null;
    }

    /**
     * @param object $entity
     * @param array $data
     * @return BlockListener
     */
    public function setOriginBlockProperties(object $entity, array $data): BlockListener
    {
        $oid = spl_object_hash($entity);
        $this->originEntities[$oid] = $data;
        return $this;
    }
}
