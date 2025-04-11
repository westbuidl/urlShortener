<?php

namespace App\Utils;

class Base62
{
    private static $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public static function encode($number)
    {
        if ($number === 0) {
            return '0';
        }

        $result = '';
        while ($number > 0) {
            $result = self::$charset[$number % 62] . $result;
            $number = (int) ($number / 62);
        }

        return $result;
    }

    public static function decode($str)
    {
        $number = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $number = $number * 62 + strpos(self::$charset, $str[$i]);
        }

        return $number;
    }
}