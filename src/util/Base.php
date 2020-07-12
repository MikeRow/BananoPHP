<?php

namespace mikerow\php4nano\util;

// from here > http://www.danvk.org/hex2dec.html

/**
 * A function for converting hex <-> dec w/o loss of precision.
 *
 * The problem is that parseInt("0x12345...") isn't precise enough to convert
 * 64-bit integers correctly.
 *
 * Internally, this uses arrays to encode decimal digits starting with the least
 * significant:
 * 8 = [8]
 * 16 = [6, 1]
 * 1024 = [4, 2, 0, 1]
 */

class Base
{
    public static function add($x, $y, $base)
    {
        $z = [];
        $n = max(count($x), count($y));
        $carry = 0;
        $i = 0;
        while ($i < $n || $carry)
        {
            $xi = $i < count($x) ? $x[$i] : 0;
            $yi = $i < count($y) ? $y[$i] : 0;
            $zi = $carry + $xi + $yi;
            $z[] = $zi % $base;
            $carry = floor($zi / $base);
            $i++;
        }
        return $z;
    }
    
    public static function multiplyByNumber($num, $x, $base) {
        if ($num < 0) return null;
        if ($num == 0) return [];
        
        $result = [];
        $power = $x;
        while (true)
        {
            if ($num & 1)
            {
                $result = self::add($result, $power, $base);
            }
            $num = $num >> 1;
            if ($num === 0)
                break;
                $power = self::add($power, $power, $base);
        }
        
        return $result;
    }
    
    public static function parseToDigitsArray($str, $base) {
        $digits = str_split($str);
        $ary = [];
        for ($i = count($digits) - 1; $i >= 0; $i--)
        {
            $n = intval($digits[$i], $base);
            if (!is_numeric($n)) return null;
            $ary[] = $n;
        }
        return $ary;
    }
    
    public static function convertBase($str, $fromBase, $toBase) {
        $digits = self::parseToDigitsArray($str, $fromBase);
        if ($digits === null) return null;
        
        $outArray = [];
        $power = [1];
        for ($i = 0; $i < count($digits); $i++) {
            // invariant: at this point, fromBase^i = power
            if ($digits[$i])
            {
                $outArray = self::add($outArray, self::multiplyByNumber($digits[$i], $power, $toBase), $toBase);
            }
            $power = self::multiplyByNumber($fromBase, $power, $toBase);
        }
        
        $out = '';
        for ($i = count($outArray) - 1; $i >= 0; $i--) {
            $out .= base_convert($outArray[$i], $fromBase, $toBase);
        }
        return $out;
    }
    
    public static function decToHex($decStr) {
        $hex = self::convertBase($decStr, 10, 16);
        if(strlen($hex) % 2 != 0)
            $hex = '0' . $hex;
        return $hex ? $hex : null;
    }
    
    public static function hexToDec($hexStr) {
        if (substr($hexStr, 0, 2) === '0x') $hexStr = substr($hexStr, 2);
        $hexStr = strtolower($hexStr);
        return self::convertBase($hexStr, 16, 10);
    }
}
