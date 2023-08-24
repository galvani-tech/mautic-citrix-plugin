<?php

declare(strict_types=1);

namespace MauticPlugin\MauticCitrixBundle\Helper;

abstract class BasicEnum
{
    private static ?array $constCacheArray = null;

    private static function getConstants()
    {
        if (null === self::$constCacheArray) {
            self::$constCacheArray = [];
        }
        $calledClass = static::class;
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect                             = new \ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return self::$constCacheArray[$calledClass];
    }

    /**
     * @param bool $strict
     *
     * @return bool
     */
    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));

        return in_array(strtolower($name), $keys, true);
    }

    /**
     * @param bool $strict
     *
     * @return bool
     */
    public static function isValidValue($value, $strict = true)
    {
        $values = array_values(self::getConstants());

        return in_array($value, $values, $strict);
    }

    /**
     * @return array
     */
    public static function toArray()
    {
        return array_values(self::getConstants());
    }

    /**
     * @return array
     */
    public static function toArrayOfNames()
    {
        return array_keys(self::getConstants());
    }
}
