<?php

namespace mikerow\php4nano\util;

// # Integer array to binary string

function arr2bin(array $array): string
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


// # Binary string to integer array

function bin2arr(string $string): array
{
    return array_map('ord', str_split($string));
}
