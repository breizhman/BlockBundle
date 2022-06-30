<?php

namespace Cms\BlockBundle\Service\Entity;

use App\Entity\AdvertLocation;
use Cms\BlockBundle\Entity\Block;
use Cms\BlockBundle\Exception\NotFoundException;
use Cms\BlockBundle\Repository\BlockRepository;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Model\Type\BlockTypeInterface;
use Cms\BlockBundle\Serializer\Encoder\ArrayEncoder;
use Cms\BlockBundle\Service\BlockRegistriesInterface;
use Cms\BlockBundle\Service\Finder\AnnotationsFinderInterface;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BlockEntityManager
 *
 * @package Cms\BlockBundle\Service\Entity
 */
class BlockEntityManager implements BlockEntityManagerInterface
{
    /**
     * @var array
     */
    protected $blocksLoaded = [];

    /**
     * @var array
     */
    protected $blocksIsLoading = [];

    /**
     * @var array
     */
    protected $blocksOriginal = [];

    /**
     * @var array
     */
    protected $blocksToInsert = [];

    /**
     * @var array
     */
    protected $blocksToUpdate = [];

    /**
     * @var array
     */
    protected $blocksToRemove = [];

    /**
     * @var BlockRegistriesInterface
     */
    protected $registries;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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
     * BlockEntityManager constructor.
     *
     * @param BlockRegistriesInterface        $registries
     * @param EntityManagerInterface          $entityManager
     * @param BlockEntityTransformerInterface $entityTransformer
     * @param BlockEntityProperty             $property
     * @param SerializerInterface             $serializer
     * @param AnnotationsFinderInterface      $annotationsFinder
     */
    public function __construct(
        BlockRegistriesInterface $registries,
        EntityManagerInterface $entityManager,
        BlockEntityTransformerInterface $entityTransformer,
        BlockEntityProperty $property,
        SerializerInterface $serializer,
        AnnotationsFinderInterface $annotationsFinder
    )
    {
        $this->registries = $registries;
        $this->entityManager = $entityManager;
        $this->entityTransformer = $entityTransformer;
        $this->property = $property;
        $this->serializer = $serializer;
        $this->annotationsFinder = $annotationsFinder;
    }

    /**
     * @inheritdoc
     */
    public function create($nameOrClass, array $data = [], BlockEntityInterface $parentBlock = null): ?BlockEntityInterface
    {
        return $this->createByNameAndData($nameOrClass, $data, $parentBlock ? $parentBlock->getBlockId() : null);

    }

    /**
     * @inheritdoc
     */
    public function load(string $id): ?BlockEntityInterface
    {
        if (isset($this->blocksLoaded[$id])) {
            return $this->blocksLoaded[$id];
        }

        $data = $this->getRepository()->findDataById($id);
        if (empty($data)) {
            throw new NotFoundException(sprintf('Block with ID %s not found', $id));
        }

        $this->blocksIsLoading[$id] = $data;

        $blockEntity = $this->createByNameAndData($data['blockType'], $data);

        unset($this->blocksIsLoading[$id]);

        return $blockEntity;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param null|BlockEntityInterface $parentBlockEntity
     *
     * @throws NotFoundException
     */
    protected function initEntity(BlockEntityInterface $blockEntity, BlockEntityInterface $parentBlockEntity = null): void
    {
        if (!$blockEntity->getBlockId()) {
            $blockEntity->setBlockId($this->generateId());
        }

        if (!$blockEntity->getBlockType()) {
            $blockType = $this->getTypeByEntityClass(get_class($blockEntity));
            if (!$blockType) {
                throw new NotFoundException(sprintf('No block type class found for entity "%s"', get_class($blockEntity)));
            }

            $blockEntity->setBlockType($blockType->getName());
        }

        if ($parentBlockEntity) {
            $blockEntity->setParentBlockId($parentBlockEntity->getBlockId());
        }
    }

    /**
     * @param string $nameOrClass
     * @param array  $data
     * @param null|string $parentBlockId
     *
     * @return BlockEntityInterface|null
     */
    protected function createByNameAndData(string $nameOrClass, array $data = [], string $parentBlockId = null): ?BlockEntityInterface
    {
        $blockEntityClass = null;
        if (class_exists($nameOrClass) && is_subclass_of($nameOrClass, BlockEntityInterface::class)) {
            $blockEntityClass = $nameOrClass;
        }

        if (!$blockEntityClass) {
            $blockEntityClass = $this->getType($nameOrClass)->getEntity();
        }

        $blockEntity = $this->serializer->deserialize($data, $blockEntityClass, ArrayEncoder::FORMAT, [
            ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            'parent_block_id' => $parentBlockId,
        ]);

        if (!$blockEntity instanceof BlockEntityInterface) {
            return null;
        }

        $this->initEntity($blockEntity);

        return $blockEntity;
    }

    /**
     * @inheritdoc
     */
    public function toArray(BlockEntityInterface $blockEntity): ?array
    {
        $data = $this->serializer->serialize($blockEntity, JsonEncoder::FORMAT);
        try {
            return json_decode($data, true);
        } catch (\Throwable $t) {
            return null;
        }
    }


    /**
     * @inheritdoc
     */
    public function persist(BlockEntityInterface $blockEntity, BlockEntityInterface $parentBlockEntity = null): BlockEntityManagerInterface
    {
        $this->initEntity($blockEntity, $parentBlockEntity);

        $this->prepareForPersist($blockEntity);

        // case entity is doctrine entity : persist direct
        if ($this->isEntity($blockEntity)) {
            $this->entityTransformer->persist($blockEntity);

            return $this;
        }

        $this->entityTransformer->persist($blockEntity, [], [Target::TARGET_CLASS]);

        // check entity property update
        $propertiesState = $this->getPropertiesNameByState($blockEntity, [BlockEntityProperty::STATE_ADD, BlockEntityProperty::STATE_UPDATE, BlockEntityProperty::STATE_DELETE]);
        if (!$propertiesState) {
            return $this;
        }

        // run action persist or remove on property
        foreach ($propertiesState as $state => $propertiesName) {
            if (in_array($state, [BlockEntityProperty::STATE_ADD, BlockEntityProperty::STATE_UPDATE], true)) {
                $blockEntity = $this->entityTransformer->persist($blockEntity, $propertiesName);
            }

            if (!$this->isNew($blockEntity) && $state === BlockEntityProperty::STATE_DELETE) {
                $this->entityTransformer->remove($blockEntity, $propertiesName);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function remove(BlockEntityInterface $blockEntity): BlockEntityManagerInterface
    {
        $this->prepareForRemove($blockEntity);

        $this->entityTransformer->remove($blockEntity);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function flush(): BlockEntityManagerInterface
    {
        /** @var BlockEntityInterface $blockEntity */
        foreach ($this->blocksToInsert as $blockEntity) {
            $this->getRepository()->insert($this->toArray($blockEntity));

            $this->clearPrepare($blockEntity);
            $this->register($blockEntity);
        }

        foreach ($this->blocksToUpdate as $blockEntity) {
            $this->getRepository()->update($this->toArray($blockEntity));

            $this->clearPrepare($blockEntity);
            $this->register($blockEntity);
        }

        foreach ($this->blocksToRemove as $blockEntity) {
            $this->getRepository()->delete($this->toArray($blockEntity));

            $this->clearPrepare($blockEntity);
            $this->register($blockEntity);
        }

        return $this;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     */
    public function register(BlockEntityInterface $blockEntity): void
    {
        $key = $blockEntity->getBlockId() ?? spl_object_hash($blockEntity);

        $this->blocksLoaded[$key] = $blockEntity;
        $this->blocksOriginal[$key] = clone $blockEntity;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     */
    protected function clearPrepare(BlockEntityInterface $blockEntity): void
    {
        $oid = spl_object_hash($blockEntity);

        unset($this->blocksToInsert[$oid], $this->blocksToUpdate[$oid], $this->blocksToRemove[$oid]);
    }

    /**
     * @param BlockEntityInterface $blockEntity
     */
    protected function prepareForPersist(BlockEntityInterface $blockEntity): void
    {
        $oid = spl_object_hash($blockEntity);
        $this->clearPrepare($blockEntity);

        if (!$this->isNew($blockEntity)) {
            $this->blocksToUpdate[$oid] = $blockEntity;

            return;
        }

        $this->blocksToInsert[$oid] = $blockEntity;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     */
    protected function prepareForRemove(BlockEntityInterface $blockEntity): void
    {
        $this->clearPrepare($blockEntity);

        $this->blocksToRemove[spl_object_hash($blockEntity)] = $blockEntity;
    }


    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return BlockEntityInterface
     */
    protected function getOriginEntity(BlockEntityInterface $blockEntity): ?BlockEntityInterface
    {
        if ($blockEntity->getBlockId() && isset($this->blocksOriginal[$blockEntity->getBlockId()])) {
            return $this->blocksOriginal[$blockEntity->getBlockId()];
        }

        return $this->blocksOriginal[spl_object_hash($blockEntity)] ?? null;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return bool
     */
    public function hasChanged(BlockEntityInterface $blockEntity): bool
    {
        try {
            return count($this->getPropertiesByState($blockEntity, [
                    BlockEntityProperty::STATE_ADD,
                    BlockEntityProperty::STATE_UPDATE,
                    BlockEntityProperty::STATE_DELETE,
                ])) > 0;
        } catch (\Throwable $t) {
            return false;
        }
    }

    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return bool
     */
    public function isNew(BlockEntityInterface $blockEntity): bool
    {
        return (
            !$this->isLoading($blockEntity)
            &&
            $this->getOriginEntity($blockEntity) === null
        );
    }

    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return bool
     */
    public function isLoading(BlockEntityInterface $blockEntity): bool
    {
        $key = $blockEntity->getBlockId() ?? spl_object_hash($blockEntity);
        return isset($this->blocksIsLoading[$key])
            || (
                $blockEntity->getParentBlockId()
                &&
                isset($this->blocksIsLoading[$blockEntity->getParentBlockId()])
            );
    }

    /**
     * @param BlockEntityInterface $blockEntity
     *
     * @return bool
     */
    public function isLoaded(BlockEntityInterface $blockEntity): bool
    {
        $key = $blockEntity->getBlockId() ?? spl_object_hash($blockEntity);

        return isset($this->blocksLoaded[$key]);
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isEntity(object $entity): bool
    {
        $class = ($entity instanceof Proxy)
            ? get_parent_class($entity)
            : get_class($entity);

        return !$this->entityManager->getMetadataFactory()->isTransient($class);
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param array                $filterStates
     * @param array                $filterProperties
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

            if ($reflectionProperty->getName() === 'blockId') {
                continue;
            }

            if ($reflectionProperty->getName() === 'blockName') {
                continue;
            }

            // case new entity managed by doctrine
            if ($this->isEntity($blockEntity)) {

                // case entity must be insert into bdd, all property to ADD
                if ($this->entityManager->getUnitOfWork()->isScheduledForInsert($blockEntity)) {
                    $propertiesStates[BlockEntityProperty::STATE_ADD][] = $reflectionProperty;
                    continue;
                }

                // case entity must be delete into bdd, all property to DELETE
                if ($this->entityManager->getUnitOfWork()->isScheduledForDelete($blockEntity)) {
                    $propertiesStates[BlockEntityProperty::STATE_DELETE][] = $reflectionProperty;
                    continue;
                }
            }

            // case new entity, all property has add state
            if (!$originBlockEntity) {
                $propertiesStates[BlockEntityProperty::STATE_ADD][] = $reflectionProperty;
                continue;
            }

            // case entity already exist, compare all property
            $oldValue = $this->property->getValue($originBlockEntity, $reflectionProperty->getName());
            $newValue = $this->property->getValue($blockEntity, $reflectionProperty->getName());

            $state = $this->property->compare($oldValue, $newValue);

            $propertiesStates[$state][] = $reflectionProperty;
        }

        // exclude status not in $filterStatus
        if ($propertiesStates && $filterStates) {
            $propertiesStates = array_filter($propertiesStates, static function ($status) use ($filterStates) {
                return in_array($status, $filterStates, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $propertiesStates;
    }

    /**
     * @param BlockEntityInterface $blockEntity
     * @param array                $filterStates
     * @param array                $filterProperties
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function getPropertiesNameByState(BlockEntityInterface $blockEntity, $filterStates = [], $filterProperties = []): ?array
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
     * @param array                $filterProperties
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    protected function getReflectionProperties(BlockEntityInterface $blockEntity, $filterProperties = []): array
    {
        $properties = (new \ReflectionClass(get_class($blockEntity)))->getProperties();

        if ($filterProperties) {
            $properties = array_filter($properties, function ($property) use ($filterProperties) {
                /** @var \ReflectionProperty $property */
                return in_array($property->getName(), $filterProperties, true);
            });
        }

        return $properties;
    }

    /**
     * @param string $nameOrClass
     *
     * @return BlockTypeInterface
     */
    protected function getType(string $nameOrClass): BlockTypeInterface
    {
        $registry = $this->registries->getRegistry('type');
        if (!$registry->has($nameOrClass)) {
            throw new \RuntimeException(sprintf(' Not find block class entity with name %s', $nameOrClass));
        }

        return $registry->get($nameOrClass);
    }

    /**
     * @param string $blockEntityClass
     *
     * @return BlockTypeInterface|null
     */
    protected function getTypeByEntityClass(string $blockEntityClass): ?BlockTypeInterface
    {
        $registry = $this->registries->getRegistry('type');

        foreach ($registry->getClassNames() as $blockTypeClass) {

            /** @var BlockTypeInterface $blockType */
            $blockType = $registry->get($blockTypeClass);
            if ($blockType->getEntity() === $blockEntityClass) {
                return $blockType;
            }
        }

        return null;
    }

    /**
     * generate unique identify for block entity
     *
     * @return string
     */
    protected function generateId(): string
    {
        return uniqid(date('YmdHms'), false);
    }

    /**
     * @return BlockRepository
     */
    protected function getRepository(): BlockRepository
    {
        return $this->entityManager->getRepository(Block::class);
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return BlockEntityProperty
     */
    public function getProperty(): BlockEntityProperty
    {
        return $this->property;
    }
}