<?php

namespace Cms\BlockBundle\Serializer\Normalizer;

use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\Entity\BlockEntityTransformerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class BlockNormalizer
 * @package BlockBundle\Serializer\Normalizer
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
     * @param ObjectNormalizer $objectNormalizer
     * @param BlockEntityTransformerInterface $entityTransformer
     */
    public function __construct(ObjectNormalizer $objectNormalizer, BlockEntityTransformerInterface $entityTransformer)
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->entityTransformer = $entityTransformer;
    }

    /**
     * @inheritDoc
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($object instanceof  BlockEntityInterface) {
            $object = $this->entityTransformer->reverseTransform($object);
        }

        return $this->objectNormalizer->normalize($object, $format, $context);
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
        $object = $this->objectNormalizer->denormalize($data, $class, $format, $context);
        if ($object instanceof  BlockEntityInterface) {
            $object = $this->entityTransformer->transform($object);
        }

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