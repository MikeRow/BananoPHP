<?php 

// *** Merge array2 to array1, only missing elements ***


function array_merge_new_recursive( array $array1, array $array2 )
{
    foreach( $array2 as $key => $value )
    {
        if( is_array( $value ) && isset( $array1[$key] ) && is_array( $array1[$key] ) )
        {
            $array1[$key] = array_merge_new_recursive( $array1[$key], $value );
        }
        else
        {
            if( !isset( $array1[$key] ) )
            {
                $array1[$key] = $value;
            }
        }
    }
    
    return $array1;
}
