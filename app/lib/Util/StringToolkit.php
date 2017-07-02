<?php

namespace Dailex\Util;

use DateTimeInterface;
use Exception;

class StringToolkit
{
    public static function asStudlyCaps(string $value): string
    {
        $value = ucwords(str_replace(array('_', '-'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    public static function asCamelCase(string $value): string
    {
        return lcfirst(self::asStudlyCaps($value));
    }

    public static function asSnakeCase(string $value): string
    {
        return ctype_lower($value) ? $value : mb_strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $value));
    }

    public static function endsWith(string $haystack, $needle): bool
    {
        $needles = (array)$needle;

        foreach ($needles as $needle) {
            $length = mb_strlen($needle);

            if ($length == 0 || mb_substr($haystack, -$length, $length) === $needle) {
                return true;
            }
        }

        return false;
    }

    public static function startsWith(string $haystack, $needle): bool
    {
        $needles = (array)$needle;

        foreach ($needles as $needle) {
            $length = mb_strlen($needle);
            if ($length == 0 || mb_substr($haystack, 0, $length) === $needle) {
                return true;
            }
        }

        return false;
    }

    public static function getAggregateRootPrefix(string $aggregateRoot)
    {
        $parts = explode('\\', $aggregateRoot, 4);
        return sprintf(
            '%s.%s.%s',
            self::asSnakeCase($parts[0]),
            self::asSnakeCase($parts[1]),
            self::asSnakeCase($parts[2])
        );
    }
}
