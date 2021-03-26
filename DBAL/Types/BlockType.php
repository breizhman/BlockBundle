<?php

namespace Cms\BlockBundle\DBAL\Types;

use Cms\BlockBundle\EventListener\BlockFactoryAccessorListener;
use Cms\BlockBundle\Model\Entity\BlockEntityInterface;
use Cms\BlockBundle\Service\BlockFactoryInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

/**
 * Class BlockType
 *
 * @package Cms\BlockBundle\DBAL\Types
 */
class BlockType extends StringType
{
    /**
     * @var string
     */
    public const TYPE = 'block';

    /**
     * @var BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     * @param                  $value
     * @param AbstractPlatform $platform
     *
     * @return BlockEntityInterface|mixed|null
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        try {
            $this->initBlockFactory($platform);

            return $this->blockFactory->loadEntity($value);

        } catch (\Throwable $t) {
            throw new ConversionException(sprintf("Could not convert PHP value '%s' to type '%s': %s", $value, self::TYPE, $t->getMessage()));
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
        if (!$value instanceof BlockEntityInterface) {
            return null;
        }

        try {
            $this->initBlockFactory($platform);

            return $value->getBlockId();
        } catch (\Throwable $t) {
            throw new ConversionException(sprintf("Could not convert database value '%s' to Doctrine Type '%s': %s", $value, self::TYPE, $t->getMessage()));
        }
    }

    /**
     * @param AbstractPlatform $platform
     */
    private function initBlockFactory(AbstractPlatform $platform): void
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