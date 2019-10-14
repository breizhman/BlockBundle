<?php

namespace BlockBundle\Service\Entity;

use BlockBundle\Model\Entity\BlockEntityInterface;
use BlockBundle\Serializer\Encoder\ArrayEncoder;
use BlockBundle\Service\Finder\AnnotationsFinderInterface;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BlockEntityManager
 * @package BlockBundle\Service\Entity
 */
class BlockEntityManager implements BlockEntityManagerInterface
{
    /**
     * @var array
     */
    protected $registerEntities = [];

    /**
     * @var BlockEntityTransformerInterface
     */
    protected $entityTransformer;

    /**
     * @var BlockEntityProperty
     */
    protected $property;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var AnnotationsFinderInterface
     */
    protected $annotationsFinder;

    /**
     * @var BlockIndexationManagerInterface
     */
    protected $blockIndexationManager;

    /**
     * BlockEntityManager constructor.
     * @param BlockEntityTransformerInterface $entityTransformer
     * @param BlockEntityProperty $property
     * @param SerializerInterface $serializer
     * @param AnnotationsFinderInterface $annotationsFinder
     * @param BlockIndexationManagerInterface $blockIndexationManager
     */
    public function __construct(
        BlockEntityTransformerInterface $entityTransformer,
        BlockEntityProperty $property,
        SerializerInterface $serializer,
        AnnotationsFinderInterface $annotationsFinder,
        BlockIndexationManagerInterface $blockIndexationManager
    ) {
        $this->entityTransformer = $entityTransformer;
        $this->property = $property;
        $this->serializer = $serializer;
        $this->annotationsFinder = $annotationsFinder;
        $this->blockIndexationManager = $blockIndexationManager;
    }

    /**
     * @inheritdoc
     */
    public function load($blockEntityClass, array $data = []):? BlockEntityInterface
    {
        $blockEntity = $this->serializer->deserialize($data, $blockEntityClass, ArrayEncoder::FORMAT);
        if ($blockEntity instanceof BlockEntityInterface) {
            // case entity is doctrine table : not override ID entity
            if (empty($this->annotationsFinder->findForClass($blockEntity, [Table::class])) && !$blockEntity->getId()) {
                $blockEntity->setId($this->generateId());
            }
            $this->registerEntities[$blockEntity->getId()] = clone $blockEntity;
            return $blockEntity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function toArray(BlockEntityInterface $blockEntity):? array
    {
        $data = $this->serializer->serialize($blockEntity, ArrayEncoder::FORMAT);
        if (is_array($data)) {
            return $data;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function persist(BlockEntityInterface $blockEntity, bool $indexation = true): BlockEntityManagerInterface
    {
        // generate id if not exist
        if (!$blockEntity->getId()) {
            $blockEntity->setId($this->generateId());
        }

        // case entity is doctrine entity : persist direct
        if (!empty($this->annotationsFinder->findForClass($blockEntity, [Table::class]))) {
            $this->entityTransformer->persist($blockEntity);
        } else {

            $this->entityTransformer->persist($blockEntity, [], [Target::TARGET_CLASS]);

            // check entity property update
            $originBlockEntity = $this->getOriginEntity($blockEntity);
            $propertiesState = $this->getPropertiesNameByState($blockEntity, [BlockEntityProperty::STATE_ADD, BlockEntityProperty::STATE_UPDATE, BlockEntityProperty::STATE_DELETE]);

            // run action persist or remove on property
            if ($propertiesState) {
                foreach ($propertiesState as $state => $propertiesName) {

                    if (in_array($state, [BlockEntityProperty::STATE_ADD, BlockEntityProperty::STATE_UPDATE])) {
                        $blockEntity = $this->entityTransformer->persist($blockEntity, $propertiesName);
                    }

                    if ($originBlockEntity && in_array($state, [BlockEntityProperty::STATE_DELETE])) {
                        $this->entityTransformer->remove($originBlockEntity, $propertiesName);
                    }
                }
            }
        }

        if ($indexation) {
            $this->persistIndexation($blockEntity);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function persistIndexation(BlockEntityInterface $blockEntity): BlockEntityManagerInterface
    {
        $this->blockIndexationManager->persist($blockEntity);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function remove(BlockEntityInterface $blockEntity): BlockEntityManagerInterface
    {
        $this->entityTransformer->remove($blockEntity);
        $this->removeIndexation($blockEntity);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeIndexation(BlockEntityInterface $blockEntity): BlockEntityManagerInterface
    {
        $this->blockIndexationManager->remove($blockEntity);
        return $this;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return BlockEntityInterface
     */
    protected function getOriginEntity(BlockEntityInterface $blockEntity):? BlockEntityInterface
    {
        return $this->registerEntities[$blockEntity->getId()] ?? null;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param array $filterStates
     * @param array $filterProperties
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function getPropertiesByState(BlockEntityInterface $blockEntity, $filterStates = [], $filterProperties = []): array
    {
        $propertiesStates = [];
        $originBlockEntity = $this->getOriginEntity($blockEntity);

        /** @var \ReflectionProperty $reflectionProperty */
        foreach ($this->getReflectionProperties($blockEntity, $filterProperties) as $reflectionProperty) {
            // case entity already exist, compare all property
            if ($originBlockEntity) {
                $oldValue = $this->property->getValue($originBlockEntity, $reflectionProperty->getName());
                $newValue = $this->property->getValue($blockEntity, $reflectionProperty->getName());

                $state = $this->property->compare($oldValue, $newValue);
            // case new entity, all property has add state
            } else {
                $state = BlockEntityProperty::STATE_ADD;
            }

            $propertiesStates[$state][] = $reflectionProperty;
        }

        // exclude status not in $filterStatus
        if ($propertiesStates && $filterStates) {
            $propertiesStates = array_filter($propertiesStates, function($status) use ($filterStates) {
                return in_array($status, $filterStates);
            }, ARRAY_FILTER_USE_KEY );
        }

        return $propertiesStates;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param array $filterStates
     * @param array $filterProperties
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function getPropertiesNameByState(BlockEntityInterface $blockEntity, $filterStates = [], $filterProperties = []):? array
    {
        $propertiesByState = $this->getPropertiesByState($blockEntity, $filterStates, $filterProperties);
        if ($propertiesByState) {
            return array_map(function ($properties) {
                return array_map(function ($property) {
                    /** @var \ReflectionProperty $property */
                    return $property->getName();
                }, $properties);
            }, $propertiesByState);
        }

        return null;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param array $filterProperties
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function getReflectionProperties(BlockEntityInterface $blockEntity, $filterProperties = []): array
    {
        $properties = ( new \ReflectionClass(get_class($blockEntity)) )->getProperties();

        if ($filterProperties) {
            $properties = array_filter($properties, function($property) use ($filterProperties) {
                /** @var \ReflectionProperty $property */
                return in_array($property->getName(), $filterProperties);
            });
        }

        return $properties;
    }

    /**
     * @inheritdoc
     */
    public function findOneById($id):? BlockEntityInterface
    {
        return $this->registerEntities[$id] ?? null;
    }

    /**
     * generate unique identify for block entity

     * @return string
     */
    public function generateId(): string
    {
        return uniqid();
    }

    /**
     * @return BlockEntityProperty
     */
    public function getProperty(): BlockEntityProperty
    {
        return $this->property;
    }

}