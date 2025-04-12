<?php

namespace App\Utils;

class Base62
{
    private static $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**

     * 
     * @param int $number
     * @param int $length Minimum length of the output string (default: 6)
     * @return string
     */
    public static function encode($number, $length = 6)
    {
       
        $seed = ($number * 1103515245 + 12345) & 0x7fffffff;
        
        if ($number === 0) {
            return str_pad('0', $length, '0');
        }

        $result = '';
        
       
        $base = '';
        $temp = $number;
        while ($temp > 0) {
            $base = self::$charset[$temp % 62] . $base;
            $temp = (int) ($temp / 62);
        }
        
        
        $result = $base;
        
        while (strlen($result) < $length) {
            $seed = ($seed * 48271) % 2147483647;
            $result .= self::$charset[$seed % 62];
        }
        
        return $result;
    }

    /**
     
     * 
     * @param string $str
     * @return int
     */
    public static function decode($str)
    {
        
        $maxIndex = 0;
        $temp = $str;
        
        while (strlen($temp) > 0) {
            $char = $temp[0];
            $temp = substr($temp, 1);
            
            if (self::isValidBase62Char($char)) {
                $maxIndex++;
            } else {
                break;
            }
        }
        
        
        $validStr = substr($str, 0, $maxIndex);
        
        $number = 0;
        for ($i = 0; $i < strlen($validStr); $i++) {
            $char = $validStr[$i];
            if (self::isValidBase62Char($char)) {
                $number = $number * 62 + strpos(self::$charset, $char);
            }
        }
        
        return $number;
    }
    
    /**
    
     * 
     * @param string $char
     * @return bool
     */
    private static function isValidBase62Char($char)
    {
        return strpos(self::$charset, $char) !== false;
    }
    
    /**
    
     * 
     * @param int $length
     * @return string
     */
    public static function random($length = 6)
    {
        $result = '';
        $max = strlen(self::$charset) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $result .= self::$charset[mt_rand(0, $max)];
        }
        
        return $result;
    }
}