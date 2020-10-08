<?php

namespace MikeRow\Bandano\Util;

class Bin
{
    public static function arr2bin(array $array)
    {
        foreach ($array as $value) {
            if (!ctype_digit((string) $value)) {
                return false;
            }
            if ($value < 0 || $value > 255) {
                return false;
            }
        }
        
        return implode(array_map('chr', $array));
    }    
    
    public static function bin2arr(string $string): array
    {
        return array_map('ord', str_split($string));
    }
}
