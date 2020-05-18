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
	
	
	// *** Custom number format ***
	
	
	function custom_number( $number, $decimals = -1 )
	{
		global $C;
		global $C2;
		
		// $number = sprintf( "%s", $number );
		
		if( $decimals < 0 )
		{
			$amount_array = explode( '.', $number );
			
			if( isset( $amount_array[1] ) )
			{
				// Remove useless decimals
				
				while( substr( $amount_array[1], -1 ) == '0' )
				{
					$amount_array[1] = substr( $amount_array[1], 0, -1 );
				}
				
				if( strlen( $amount_array[1] ) < 1 )
				{
					return number_format( $amount_array[0], 0, '', $C['format']['thousand'] );
				}
				else
				{
					return number_format( $amount_array[0], 0, '', $C['format']['thousand'] ) . '.' . $amount_array[1];
				}
			}
			else
			{
				return number_format( floor( $number ), 0, '', $C['format']['thousand'] );
			}
		}
		elseif( $decimals == 0 )
		{
			return number_format( floor( $number ), 0, $C['format']['decimal'], $C['format']['thousand'] );
		}
		else
		{
			$amount = number_format( $number, $decimals, $C['format']['decimal'], $C['format']['thousand'] );
			
			// Remove useless decimals
			
			while( substr( $amount, -1 ) == '0' )
			{
				$amount = substr( $amount, 0, -1 );
			}
			
			// Remove dot if all decimals are zeroes
			
			if( substr( $amount, -1 ) == '.' )
			{
				$amount = substr( $amount, 0, -1 );
			}
			
			return $amount;
		}
	}
	
	
	// *** ncmCall ***
	
	
	function ncmCall( &$ssh, string $ncm_path, string $command, array $arguments, string $flags = '', string $callerID = 'remote-script' )
	{
		if( $flags != '' ) $flags .= ',';
		
		$flags .= 'json_in,json_out,no_confirm';
		
		$return = $ssh->exec( "php $ncm_path $command '" . json_encode( $arguments ) . "' flags=$flags callerID=$callerID" . PHP_EOL );
		
		return json_decode( $return, true );
	}

?>