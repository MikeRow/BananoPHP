<?php

// # Custom number format

function custom_number($number, int $decimals = -1, $decimal = null, $thousand = null)
{
    // $number = sprintf( "%s", $number );
    
    if ($decimals == 0) {
        return number_format(floor($number), 0, $decimal, $thousand);
    } else {
        $amount_array = explode('.', $number);
        
        if (isset($amount_array[1])) {
            // Remove useless decimals
            
            while (substr($amount_array[1], -1) == '0') {
                $amount_array[1] = substr($amount_array[1], 0, -1);
            }
            
            if (strlen($amount_array[1]) < 1) {
                return number_format($amount_array[0], 0, '', $thousand);
            } else {
                if ($decimals < 0) {
                    return number_format(
                               $amount_array[0],
                               0,
                               '',
                               $thousand
                           ) . 
                           '.' .
                           $amount_array[1];
                } else {
                    return number_format(
                               $amount_array[0],
                               0,
                               '',
                               $thousand
                           ) .
                           '.' .
                           substr($amount_array[1], 0, $decimals);
                }
            }
        } else {
            return number_format(floor($number), 0, '', $thousand);
        }
    }
}
