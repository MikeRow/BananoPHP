<?php

	namespace php4nano\lib\NanoTools;
	
	require_once __DIR__ . '/../lib3/RaiBlocksPHP-master/util.php';
	require_once __DIR__ . '/../lib3/RaiBlocksPHP-master/Salt/autoload.php';
	
	use \Uint as Uint;
	use \SplFixedArray as SplFixedArray;
	use \Blake2b as Blake2b;
	
	class NanoTools
	{
		// Denominations and raw values
	
		const raw2 =
		[
			'unano' => '1000000000000000000',
			'mnano' => '1000000000000000000000',
			 'nano' => '1000000000000000000000000',
			'knano' => '1000000000000000000000000000',
			'Mnano' => '1000000000000000000000000000000',
			 'NANO' => '1000000000000000000000000000000',
			'Gnano' => '1000000000000000000000000000000000'
		];	
	
	
	
		// ***************************
		// *** Denomination to raw ***
		// ***************************
	
	
	
		public static function den2raw( $amount, string $denomination )
		{
			$raw2denomination = self::raw2[$denomination];
			
			if( $amount == 0 )
			{
				return '0';
			}
			
			if( strpos( $amount, '.' ) )
			{
				$dot_pos = strpos( $amount, '.' );
				$number_len = strlen( $amount ) - 1;
				$raw2denomination = substr( $raw2denomination, 0, - ( $number_len - $dot_pos ) );
			}
			
			$amount = str_replace( '.', '', $amount ) . str_replace( '1', '', $raw2denomination );
			
			// Remove useless zeroes from left
			
			while( substr( $amount, 0, 1 ) == '0' )
			{
				$amount = substr( $amount, 1 );	
			}
			
			return $amount;
		}
	
	
	
		// ***************************
		// *** Raw to denomination ***
		// ***************************
		
		
		
		public static function raw2den( $amount, string $denomination )
		{
			$raw2denomination = self::raw2[$denomination];
			
			if( $amount == '0' )
			{
				return 0;
			}
			
			$prefix_lenght = 39 - strlen( $amount );
			
			$i = 0;
			
			while( $i < $prefix_lenght )
			{
				$amount = '0' . $amount;
				$i++;
			}
			
			$amount = substr_replace( $amount, '.', - ( strlen( $raw2denomination ) - 1 ), 0 );
		
			// Remove useless zeroes from left
		
			while( substr( $amount, 0, 1 ) == '0' && substr( $amount, 1, 1 ) != '.' )
			{
				$amount = substr( $amount, 1 );	
			}
		
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
		
		
		
		// ************************************
		// *** Denomination to denomination ***
		// ************************************
		
		
		
		public static function den2den( $amount, string $denomination_from, string $denomination_to )
		{
			$raw = self::den2raw( $amount, $denomination_from );
			
			return self::raw2den( $raw, $denomination_to );
		}
		
		
		
		// *******************
		// *** Account key ***
		// *******************
		
		
		
		public static function account_key( string account )
		{
			if( ( strpos( $account, 'xrb_1' ) === 0 || strpos( $account, 'xrb_3' ) === 0 || strpos( $account, 'nano_1' ) === 0 || strpos( $account, 'nano_3' ) === 0 ) && ( strlen( $account ) == 64 || strlen( $account ) == 65 ) )
			{
				$crop = explode( '_', $account );
				$crop = $crop[1];
				
				if( preg_match( '/^[13456789abcdefghijkmnopqrstuwxyz]+$/', $crop ) )
				{
					$aux = Uint::fromString( substr( $crop, 0, 52 ) )->toUint4()->toArray();
					array_shift( $aux );
					$key_uint4 = $aux;
					$hash_uint8 = Uint::fromString( substr( $crop, 52, 60 ) )->toUint8()->toArray();
					$key_uint8 = Uint::fromUint4Array( $key_uint4 )->toUint8();
					
					$key_hash = new SplFixedArray( 64 );
					
					$b2b = new Blake2b();
					$ctx = $b2b->init( null, 5 );
					$b2b->update( $ctx, $key_uint8, count( $key_uint8 ) );
					$b2b->finish( $ctx, $key_hash );

					$key_hash = array_reverse( array_slice( $key_hash->toArray(), 0, 5 ) );
					
					if( $hash_uint8 == $key_hash )
					{
						return Uint::fromUint4Array( $key_uint4 )->toHexString();
					}
				}
			}
			
			return false;
		}
		
		
		
		// ************************
		// *** Account validate ***
		// ************************
		
		
		
		private static function to_uint5( $n )
		{
			$letter_list = str_split( "13456789abcdefghijkmnopqrstuwxyz" );
			
			return( array_search( $n, $letter_list ) );
		}
		
		public static function account_validate( string $account, bool $php_blake2 = false )
		{
			
			if( !$php_blake2 )
			{
				if( ( strpos( $account, 'xrb_1' ) === 0 || strpos( $account, 'xrb_3' ) === 0 || strpos( $account, 'nano_1' ) === 0 || strpos( $account, 'nano_3' ) === 0 ) && ( strlen( $account ) == 64 || strlen( $account ) == 65 ) )
				{
					$crop = explode( '_', $account );
					$crop = $crop[1];
					
					if( preg_match( '/^[13456789abcdefghijkmnopqrstuwxyz]+$/', $crop ) )
					{
						$aux = Uint::fromString( substr( $crop, 0, 52 ) )->toUint4()->toArray();
						array_shift( $aux );
						$key_uint4 = $aux;
						$hash_uint8 = Uint::fromString( substr( $crop, 52, 60 ) )->toUint8()->toArray();
						$key_uint8 = Uint::fromUint4Array( $key_uint4 )->toUint8();
						
						$key_hash = new SplFixedArray( 64 );
						
						$b2b = new Blake2b();
						$ctx = $b2b->init( null, 5 );
						$b2b->update( $ctx, $key_uint8, count( $key_uint8 ) );
						$b2b->finish( $ctx, $key_hash );
	
						$key_hash = array_reverse( array_slice( $key_hash->toArray(), 0, 5 ) );
						
						if( $hash_uint8 == $key_hash )
						{
							return true;
						}
					}
				}
				
				return false;
			}
			else
			{
				if( is_string( $account ) )
				{
					if( ( ( strpos( $account, 'xrb_1' ) === 0 ) || ( strpos( $account, 'xrb_3' ) === 0 ) || ( strpos( $account, 'nano_1' ) === 0 ) || ( strpos( $account, 'nano_3' ) === 0 ) ) && ( strlen( $account ) == 64 || strlen( $account ) == 65 ) )
					{
			
						$account = explode( '_', $account );
						$account = $account[1];
						
						$char_validation = preg_match( "/^[13456789abcdefghijkmnopqrstuwxyz]+$/", $account );
						
						if( $char_validation === 1 )
						{
							$account_array = str_split( $account );
							$uint5 = array_map( "self::to_uint5", $account_array );
							$uint8[0] = ( ( $uint5[0] << 7 ) + ( $uint5[1] << 2 ) + ( $uint5[2] >> 3 ) ) % 256;
							$uint8[1] = ( ( $uint5[2] << 5 ) + $uint5[3] ) % 256;

							for( $i = 0; $i < 7; ++$i )
							{
								$uint8[5*$i+2] = ( $uint5[8*$i+4] << 3 ) + ( $uint5[8*$i+5] >> 2 );
								$uint8[5*$i+3] = ( ( $uint5[8*$i+5] << 6 ) + ( $uint5[8*$i+6] << 1 ) + ( $uint5[8*$i+7] >> 4 ) ) % 256;
								$uint8[5*$i+4] = ( ( $uint5[8*$i+7] << 4 ) + ( $uint5[8*$i+8] >> 1 ) ) % 256;
								$uint8[5*$i+5] = ( ( $uint5[8*$i+8] << 7 ) + ( $uint5[8*$i+9] << 2 ) + ( $uint5[8*$i+10] >> 3 ) ) % 256;
								$uint8[5*$i+6] = ( ( $uint5[8*$i+10] << 5 ) + $uint5[8*$i+11] ) % 256;
							}
							
							$key = array_slice( $uint8, 0, 32 );
							$key_string = implode( array_map( "chr", $key ) );
							
							$hash = bin2hex( implode( array_map( "chr", array_reverse( array_slice( $uint8, 32, 5 ) ) ) ) );
							
							$check = blake2( $key_string, 5 );

							if( $hash === $check )
							{
								return true;
							}
						}
					}
				}

				return false;
			}
		}
	}

?>