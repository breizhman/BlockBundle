<?php

namespace Cms\BlockBundle\Serializer\Normalizer;

use Cms\BlockBundle\Event\BlockEntityEvent;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\Entity\BlockEntityTransformerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BlockNormalizer
 *
 * @package Cms\BlockBundle\Serializer\Normalizer
 */
class BlockNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @var ObjectNormalizer
     */
    protected $objectNormalizer;

    /**
     * @var BlockEntityTransformerInterface
     */
    protected $entityTransformer;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * BlockNormalizer constructor.
     *
     * @param ObjectNormalizer                $objectNormalizer
     * @param BlockEntityTransformerInterface $entityTransformer
     * @param EventDispatcherInterface        $eventDispatcher
     */
    public function __construct(
        ObjectNormalizer $objectNormalizer,
        BlockEntityTransformerInterface $entityTransformer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectNormalizer = $objectNormalizer;
        $this->entityTransformer = $entityTransformer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$object instanceof BlockEntityInterface) {
            return $this->objectNormalizer->normalize($object, $format, $context);
        }

        $object = $this->entityTransformer->reverseTransform($object);

        $result = $this->objectNormalizer->normalize($object, $format, $context);

        if (!is_array($result)) {
            return $result;
        }

        return array_filter($result, static function ($value) {
            if (is_bool($value)) {
                return true;
            }

            if (is_numeric($value)) {
                return $value !== null;
            }


            return !empty($value);
        });
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return (
            $data instanceof BlockEntityInterface
            &&
            $this->objectNormalizer->supportsNormalization($data, $format)
        );
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $parentBlockId = $context['parent_block_id'] ?? null;
        unset($context['parent_block_id']);

        $object = $this->objectNormalizer->denormalize($data, $class, $format, $context);

        if (!$object instanceof BlockEntityInterface) {
            return $object;
        }

        if ($parentBlockId && $parentBlockId !== $object->getBlockId()) {
            $object->setParentBlockId($parentBlockId);
        }

        $object = $this->entityTransformer->transform($object);

        $this->eventDispatcher->dispatch( new BlockEntityEvent($object), BlockEntityEvent::BUILD);

        return $object;
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->objectNormalizer->supportsDenormalization($data, $type, $format);
    }

    /**
     * Sets the serializer.
     *
     * @param SerializerInterface $serializer A SerializerInterface instance
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->objectNormalizer->setSerializer($serializer);
    }

}