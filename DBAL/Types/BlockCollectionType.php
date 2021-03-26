<?php

namespace Cms\BlockBundle\DBAL\Types;

use App\Entity\AdvertLocation;
use Cms\BlockBundle\Collection\BlockCollection;
use Cms\BlockBundle\EventListener\BlockFactoryAccessorListener;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;

/**
 * Class BlockCollectionType
 *
 * @package Cms\BlockBundle\DBAL\Types
 */
class BlockCollectionType extends JsonType
{
    /**
     * @var string
     */
    public const TYPE = 'block_collection';

    /**
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     * @param                  $value
     * @param AbstractPlatform $platform
     *
     * @return BlockCollection|mixed|null
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        try {
            $blockType = new BlockType();

            $this->initBlockFactory($platform);

            $value = parent::convertToPHPValue($value, $platform);

            if (!is_array($value)) {
                return null;
            }

            $blockCollection = new BlockCollection($this->blockFactory->getEntityManager());
            foreach ($value as $data) {

                $block = $blockType->convertToPHPValue($data, $platform);
                if ($block === null) {
                    continue;
                }

                $blockCollection->add($block);
            }

            return $blockCollection;
        } catch (\Throwable $t) {
            $valueToString = $value;
            if (is_array($valueToString)) {
                $valueToString = json_encode($valueToString);
            }

            throw new ConversionException(sprintf("Could not convert PHP value '%s' to type '%s': %s", $valueToString, self::TYPE, $t->getMessage()));
        }
    }

    /**
     * @param                  $value
     * @param AbstractPlatform $platform
     *
     * @return mixed|null|string
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        try {
            $blockType = new BlockType();

            $this->initBlockFactory($platform);

            $newValue = [];
            if ($value instanceof BlockCollection) {
                $value = $value->toArray();
            }

            if (!is_array($value)) {
                return null;
            }

            /** @var BlockEntityInterface $blockEntity */
            foreach ($value as $blockEntity) {

                $block = $blockType->convertToDatabaseValue($blockEntity, $platform);
                if ($block === null) {
                    continue;
                }

                $newValue[] = $block;
            }

            return parent::convertToDatabaseValue($newValue, $platform);
        } catch (\Throwable $t) {
            $valueToString = $value;
            if ($valueToString instanceof BlockCollection) {
                $valueToString = $value->toArray();
            }

            if (is_array($valueToString)) {
                $valueToString = json_encode($valueToString);
            }

            throw new ConversionException(sprintf("Could not convert database value '%s' to Doctrine Type '%s': %s", $valueToString, self::TYPE, $t->getMessage()));
        }
    }

    /**
     * @param AbstractPlatform $platform
     */
    public function initBlockFactory(AbstractPlatform $platform): void
    {
        $listeners = $platform->getEventManager()->getListeners('block_factory_accessor');
        if (count($listeners) === 0) {
            throw new \RuntimeException('No event listener found with event "block_factory_accessor"');
        }

        $listener = array_shift($listeners);
        if (!$listener instanceof BlockFactoryAccessorListener) {
            throw new \RuntimeException(sprintf('Event listener must be instantiate "%s"', BlockFactoryAccessorListener::class));
        }

        $this->blockFactory = $listener->getBlockFactory();
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return self::TYPE;
    }
}