<?php

namespace Cms\BlockBundle\Serializer\Encoder;


use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ArrayEncoder implements EncoderInterface, DecoderInterface
{
    const FORMAT = 'array';

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = array())
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = array())
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return self::FORMAT === $format;
    }
}
