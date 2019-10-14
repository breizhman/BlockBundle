<?php

namespace BlockBundle\Service\Entity;

use BlockBundle\Entity\BlockIndexation;
use BlockBundle\Model\Entity\BlockEntityInterface;
use BlockBundle\Serializer\Encoder\ArrayEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BlockEntityManager
 *
 * manage block_indexation table
 *
 * @package BlockBundle\Service\Entity
 */
class BlockIndexationManager implements BlockIndexationManagerInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * BlockIndexationManager constructor.
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function persist(BlockEntityInterface $blockEntity, $autoFlush = false): BlockIndexationManagerInterface
    {
        /** @var BlockIndexation $blockIndexation */
        $blockIndexation = $this->findByEntity($blockEntity);
        if (!$blockIndexation) {
            $blockIndexation = (new BlockIndexation())
                ->setId($blockEntity->getId())
                ->setName($blockEntity->getName())
            ;
        }

        $blockIndexation->setData($this->serializer->serialize(clone $blockEntity, ArrayEncoder::FORMAT));

        $this->entityManager->persist($blockIndexation);

        $md = $this->entityManager->getClassMetadata(get_class($blockIndexation));
        $this->entityManager->getUnitOfWork()->computeChangeSet($md, $blockIndexation);

        if ($autoFlush) {
            $this->entityManager->flush();
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function remove(BlockEntityInterface $blockEntity, $autoFlush = false): BlockIndexationManagerInterface
    {
        /** @var BlockIndexation $blockIndexation */
        $blockIndexation = $this->findByEntity($blockEntity);
        if ($blockIndexation) {

            // attached entity to entity manager
            $blockIndexation = $this->entityManager->merge($blockIndexation);
            $this->entityManager->remove($blockIndexation);

            if ($autoFlush) {
                $this->entityManager->flush();
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function findByEntity(BlockEntityInterface $blockEntity) :? BlockIndexation
    {
        return $this->findByIdAndName($blockEntity->getId(), $blockEntity->getName());
    }

    /**
     * @inheritdoc
     */
    public function findByIdAndName(string $id, string $name) :? BlockIndexation
    {
        return $this->entityManager->getRepository(BlockIndexation::class)->findOneBy([
            'id' => $id,
            'name' => $name,
        ]);
    }
}