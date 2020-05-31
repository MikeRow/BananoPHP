<?php 

// *** Sort array by key recursively ***


function ksort_recursive( array &$array )
{
    if( is_array( $array ) )
    {
        ksort( $array );
        array_walk( $array, 'ksort_recursive' );
    }
}
