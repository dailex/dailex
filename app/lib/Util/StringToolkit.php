<?php

namespace Dailex\Util;

use DateTimeInterface;
use Exception;

class StringToolkit
{
    public static function asStudlyCaps($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = ucwords(str_replace(array('_', '-'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    public static function asCamelCase($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return lcfirst(self::asStudlyCaps($value));
    }

    public static function asSnakeCase($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return ctype_lower($value) ? $value : mb_strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $value));
    }

    public static function endsWith($haystack, $needle)
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

    public static function startsWith($haystack, $needle)
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

    /**
     * Formats bytes into a human readable string.
     *
     * @param int $bytes
     *
     * @return string
     */
    public static function formatBytes($bytes)
    {
        $bytes = (int) $bytes;

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 3) . ' ' . $units[$i];
    }

    public static function generateRandomToken()
    {
        return sha1(
            sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
                )
            );
    }

    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = str_replace(
            array('Ä', 'ä', 'Ö', 'ö', 'Ü', 'ü', 'ß'),
            array('Ae', 'ae', 'Oe', 'oe', 'Ue', 'ue', 'ss'),
            $text
        );
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
