<?php

namespace Cms\BlockBundle\Service;

/**
 * Class ConvertCase
 * @package Cms\BlockBundle\Service
 */
class ConvertCase
{
    const TEST_SPACE = '/\s/';

    const TEST_SEPARATOR = '/(_|-|\.|:)/';
    const TEST_SEPARATOR_SPLITTER = '/[\W_]+(.|$)/';

    const TEST_CAMEL = '/([a-z][A-Z]|[A-Z][a-z])/';
    const TEST_CAMEL_SPLITTER = '/(.)([A-Z]+)/';

    /**
     * convert string to constant (ex: SAMPLE_TEXT_CASE)
     *
     * @param string $value
     * @return string
     */
    public static function toConstantCase(string $value): string
    {
        return strtoupper(self::toSnakeCase($value));
    }

    /**
     * convert string to dot format (ex: sample.text.case)
     *
     * @param string $value
     * @return string
     */
    public static function toDotCase(string $value): string
    {
        return preg_replace('/\s/', '.', self::toSpaceCase($value));
    }

    /**
     * convert string to kebab format (ex: sample-text-case)
     *
     * @param string $value
     * @return string
     */
    public static function toKebabCase(string $value): string
    {
        return preg_replace('/\s/', '-', self::toSpaceCase($value));
    }

    /**
     * convert string to snake format (ex: sample_text_case)
     *
     * @param string $value
     * @return string
     */
    public static function toSnakeCase(string $value): string
    {
        return preg_replace('/\s/', '_', self::toSpaceCase($value));
    }

    /**
     * convert string to pascal format (ex: SampleTextCase)
     *
     * @param string $value
     * @return string
     */
    public static function toPascalCase(string $value): string
    {
        return preg_replace_callback('/(?:^|\s)(\w)/', function($matches) {
            $letter = $matches[1];
            return strtoupper($letter);
        }, self::toSpaceCase($value));
    }

    /**
     * convert string to camel format (ex: sampleTextCase)
     *
     * @param string $value
     * @return string
     */
    public static function toCamelCase(string $value): string
    {
        return preg_replace_callback('/\s(\w)/', function($matches) {
            $letter = $matches[1];
            return strtoupper($letter);
        }, self::toSpaceCase($value));
    }

    /**
     * convert string to space format (ex: sample text case)
     *
     * @param string $value
     * @return string
     */
    public static function toSpaceCase(string $value): string
    {
        if (preg_match(self::TEST_SPACE, $value)) {
            return strtolower($value);
        }

        if (preg_match(self::TEST_SEPARATOR, $value)) {
            return strtolower(self::unSeparateCase($value));
        }

        if (preg_match(self::TEST_CAMEL, $value)) {
            return strtolower(self::unCamelizeCase($value));
        }

        return strtolower($value);
    }

    /**
     * convert separate case (kebabcase, snakecase, ...) to space format
     *
     * @param string $value
     * @return string
     */
    public static function unSeparateCase(string $value): string
    {
        return preg_replace_callback(self::TEST_SEPARATOR_SPLITTER, function($matches) {
            $next = $matches[1];
            return $next ? ' '.$next: '';
        }, $value);
    }

    /**
     * convert camel case (camalcase, pascalcase) to space format
     *
     * @param string $value
     * @return string
     */
    public static function unCamelizeCase(string $value): string
    {
        return preg_replace_callback(self::TEST_CAMEL_SPLITTER, function($matches) {
            $previous = $matches[1];
            $uppers = $matches[2];

            $uppers = strtolower($uppers);
            $uppers = str_split($uppers);
            $uppers = implode(' ', $uppers);

            return $previous . ' ' . $uppers;
        }, $value);
    }
}