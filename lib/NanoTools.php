<?php

	namespace php4nano\lib\NanoTools;
	
	require_once __DIR__ . '/../lib3/RaiBlocksPHP-master/util.php';
	require_once __DIR__ . '/../lib3/RaiBlocksPHP-master/Salt/autoload.php';
	
	use \Uint as Uint;
	use \SplFixedArray as SplFixedArray;
	use \Blake2b as Blake2b;
	use \Salt as Salt;
	use \FieldElement as FieldElement;
	use \hexToDec as hexToDec;
	use \decToHex as decToHex;
	
	class NanoTools
	{
		// *****************
		// *** Constants ***
		// *****************
	
		
		
		// *** Denomination to raw values ***
		
		
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
		
		
		// *** Block preamble ***
		
		
		const preamble = '0000000000000000000000000000000000000000000000000000000000000006';
		
		
		
		// *****************
		// *** Utilities ***
		// *****************
		
		
		
		// *** Binary array to binary string ***
		
		
		public static function bin_arr2str( $array )
		{
			return implode( array_map( 'chr', $key_uint8 ) );
		}
		
		
		// *** Binary string to binary array ***
		
		
		public static function bin_str2arr( $string )
		{
			return array_map( 'ord', str_split( $string ) );
		}
		
		
		
		// ***************************
		// *** Denomination to raw ***
		// ***************************
	
	
	
		public static function den2raw( $amount, string $denomination )
		{
			if( !array_key_exists( $denomination, self::raw2 ) ) return false;
			
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
		
		
		
		public static function raw2den( string $amount, string $denomination )
		{
			if( !array_key_exists( $denomination, self::raw2 ) ) return false;
			
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
			if( !array_key_exists( $denomination_from, self::raw2 ) ) return false;
			if( !array_key_exists( $denomination_to, self::raw2 ) ) return false;
			
			$raw = self::den2raw( $amount, $denomination_from );
			
			return self::raw2den( $raw, $denomination_to );
		}
		
		
		
		// *****************************
		// *** Account to public key ***
		// *****************************
		
		
		
		public static function account2public( string $account )
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
					
					if( !extension_loaded( 'blake2' ) )
					{
						$key_hash = new SplFixedArray( 64 );
						
						$b2b = new Blake2b();
						$ctx = $b2b->init( null, 5 );
						$b2b->update( $ctx, $key_uint8, count( $key_uint8 ) );
						$b2b->finish( $ctx, $key_hash );
						
						$key_hash = array_reverse( array_slice( $key_hash->toArray(), 0, 5 ) );
					}
					else
					{
						$key_uint8 = self::bin_arr2str( (array) $key_uint8 );
						
						$key_hash = blake2( $key_uint8, 5, null, true );
						$key_hash = self::bin_str2arr( $key_hash );
						$key_hash = array_reverse( array_slice( $key_hash, 0, 5 ) );
					}
					
					if( $hash_uint8 == $key_hash )
					{
						return Uint::fromUint4Array( $key_uint4 )->toHexString();
					}
				}
			}
			
			return false;
		}
		
		
		
		// *****************************
		// *** Public key to account ***
		// *****************************
		
		
		
		public static function public2account( string $pk )
		{
			if( strlen( $pk ) != 64 || !hex2bin( $pk ) ) return false;

			if( !extension_loaded( 'blake2' ) )
			{
				$key = Uint::fromHex( $pk );
				$checksum;
				$hash = new SplFixedArray( 64 );
				
				$b2b = new Blake2b();
				$ctx = $b2b->init( null, 5 );
				$b2b->update( $ctx, $key->toUint8(), 32 );
				$b2b->finish( $ctx, $hash );
				
				$hash = Uint::fromUint8Array( array_slice( $hash->toArray(), 0, 5 ) )->reverse();
				$checksum = $hash->toString();
			}
			else
			{
				$key = Uint::fromHex( $pk )->toUint8();
				$key = self::bin_arr2str( (array) $key );
				
				$hash = blake2( $key, 5, null, true );
				$hash = self::bin_str2arr( $hash );
				$hash = array_reverse( array_slice( $hash, 0, 5 ) );
				
				$checksum = Uint::fromUint8Array( $hash )->toString();
			}
			
			$c_account = Uint::fromHex( '0' . $pk )->toString();
			
			return 'nano_' . $c_account . $checksum;
		}
		
		
		
		// *********************************
		// *** Private key to public key ***
		// *********************************
		
		
		
		public static function private2public( string $sk )
		{
			if( strlen( $sk ) != 64 || !hex2bin( $sk ) ) return false;
		    
		    $salt = Salt::instance();
		    
		    $sk = Uint::fromHex( $sk )->toUint8();
			$pk = $salt::crypto_sign_public_from_secret_key( $sk );
			
			return Uint::fromUint8Array( $pk )->toHexString();
		}
		
		
		
		// ****************
		// *** Get keys ***
		// ****************
		
		
		
		public static function keys( bool $get_account = false )
		{
			$salt = Salt::instance();
			$keys = $salt->crypto_sign_keypair();
			$keys[0] = Uint::fromUint8Array( array_slice( $keys[0]->toArray(), 0, 32 ) )->toHexString();
			$keys[1] = Uint::fromUint8Array( $keys[1] )->toHexString();
			
			if( $get_account ) $keys[2] = self::public2account( $keys[1] );
			
			return $keys;
		}
		
		
		
		// **************************
		// *** Get keys from seed ***
		// **************************
		
		
		
		public static function seed2keys( string $seed, int $index = 0, bool $get_account = false )
		{
			if( strlen( $seed ) != 64 || !hex2bin( $seed ) ) return false;
			if( $index < 0 ) return false;
			
			$seed = Uint::fromHex( $seed )->toUint8();
			$index = Uint::fromDec( $index )->toUint8()->toArray();
			
			if( count( $index ) < 4 )
			{
				$missing_bytes = [];
				for ($i = 0; $i < ( 4 - count( $index ) ); $i++) $missing_bytes[] = 0;
				$index = array_merge( $missing_bytes, $index );
			}
			
			$index = Uint::fromUint8Array( $index )->toUint8();
			$sk = new SplFixedArray( 64 );
			
			$b2b = new Blake2b();
			$ctx = $b2b->init( null, 32 );
 			$b2b->update( $ctx, $seed, count( $seed ) );
			$b2b->update( $ctx, $index, 4 );
			$b2b->finish( $ctx, $sk );
            
			$sk = Uint::fromUint8Array( array_slice( $sk->toArray(), 0, 32 ) )->toHexString();
			$pk = self::private2public( $sk );
            
			$keys = [$sk,$pk];
			
			if( $get_account ) $keys[2] = self::public2account( $pk );
			
			return $keys;
		}
		
		
		
		// ****************************
		// *** Hash array of values ***
		// ****************************
		
		
		
		public static function hash_array( array $inputs, int $size = 64 )
		{
			if( $size <1 ) return false;
			
			$ctx = $b2b->init( null, $size );
			$hash = new SplFixedArray( 64 );
			
			foreach( $inputs as $index->$value )
			{
				if( !hex2bin( $value ) ) return false;
				
				$value = Uint::fromHex( $value )->toUint8();
				$b2b->update( $ctx, $value, count( $value ) );
			}

			$b2b->finish( $ctx, $hash );
			$hash = $hash->toArray();
			$hash = array_slice( $hash, 0, $size );
			$hash = array_reverse( $hash );
			$hash = Uint::fromUint8Array( $hash )->toHexString();
			
			return $hash;
		}
		
		
		
		// **********************
		// *** Sign a message ***
		// **********************
		
		
		
		public static function sign( $sk, $msg )
		{
			if( strlen( $sk ) != 64 || !hex2bin( $sk ) ) return false;
			if( strlen( $msg ) != 64 || !hex2bin( $msg ) ) return false;
			
			$salt = Salt::instance();
			$sk = FieldElement::fromArray( Uint::fromHex( $sk )->toUint8() );
			$pk = Salt::crypto_sign_public_from_secret_key( $sk );
			$sk->setSize( 64 );
			$sk->copy( $pk, 32, 32 );
			$msg = Uint::fromHex( $msg )->toUint8();
			$sm = $salt->crypto_sign( $msg, count( $msg ), $sk );
			
			$signature = [];
			for( $i = 0; $i < 64; $i++ ) $signature[$i] = $sm[$i];
			
			return Uint::fromUint8Array( $signature )->toHexString();
		}
		
		
		
		// ****************************
		// *** Validate a signature ***
		// ****************************
		
		
		
		public static function sign_validate( $msg, $sig, $account )
		{
			if( strlen( $msg ) != 64 || !hex2bin( $msg ) ) return false;
			if( strlen( $sig ) != 128 || !hex2bin( $sig ) ) return false;
			$pk = self::account2public( $account );
			if( !$pk ) return false;
			
			$sig = Uint::fromHex( $sig )->toUint8();
			$msg = Uint::fromHex( $msg )->toUint8();
			$pk = Uint::fromHex( $pk )->toUint8();
			
			$sm = new SplFixedArray( 64 + count( $msg) );
			$m = new SplFixedArray( 64 + count( $msg) );
			for( $i = 0; $i < 64; $i++ ) $sm[$i] = $sig[$i];
			for( $i = 0; $i < count( $msg ); $i++ ) $sm[$i+64] = $msg[$i];
			
			return Salt::crypto_sign_open2( $m, $sm, count( $sm ), $pk );
		}
		
		
		
		// ***********************
		// *** Generate a work ***
		// ***********************
		
			
		/*
		public static function work( string $hash, string $difficulty )
		{
			if( !extension_loaded( 'blake2' ) ) return false,
			if( strlen( $hash ) != 64 || !hex2bin( $hash ) ) return false;
			if( strlen( $difficulty ) != 16 || !hex2bin( $difficulty ) ) return false;
			
			$hash = hex2bin( $hash );
			$difficulty = hexdec( $difficulty );
			
			$o = 1; $start = microtime( true );
			while( true )
			{
				$rng = random_bytes( 8 );
				
				blake2b_init( $ctx, 8 );
				blake2b_update( $ctx, $rng, 8 );
				blake2b_update( $ctx, $hash, 32 );
				blake2b_final( $ctx, $work, 8 );
				//echo strlen( $work ); exit;
				//$work = strrev( substr( $work, strlen( $work )-9, 8 ) );
				// $work = sodium_bin2hex( $work );
				//echo strlen( $work ); exit;
				$work = substr( $work, 0, 8 );
				$work = strrev( $work );
				$work = bin2hex( $work );
				//$work = strrev( $work );
				
				$o++;
				if( hexdec( $work ) >= $difficulty )
				{
					echo number_format( $o / ( microtime( true ) - $start ), 0, '.', ',' ) . ' works/s'. PHP_EOL . number_format( $o, 0, '.', ',' ) . PHP_EOL . number_format( microtime( true ) - $start, 0, '.', ',' ) . ' s' . PHP_EOL;
					return $work;
				}
			}
		}
		*/
		
		
		// *********************
		// *** Validate work ***
		// *********************
		
		
		
		public static function work_validate( string $hash, string $work, string $difficulty )
		{
			if( strlen( $work ) != 16 || !hex2bin( $work ) ) return false;
			if( strlen( $hash ) != 64 || !hex2bin( $hash ) ) return false;
			if( strlen( $difficulty ) != 16 || !hex2bin( $difficulty ) ) return false;
				
			$res = new SplFixedArray( 64 );
			$workBytes = Uint::fromHex( $work )->toUint8();
			$hashBytes = Uint::fromHex( $hash )->toUint8();
			$workBytes = array_reverse( $workBytes->toArray() );
			$workBytes = SplFixedArray::fromArray( $workBytes );
			
			$blake2b = new Blake2b();
			$ctx = $blake2b->init( null, 8 );
			$blake2b->update( $ctx, $workBytes, 8 );
			$blake2b->update( $ctx, $hashBytes, 32 );
			$blake2b->finish( $ctx, $res );
			
			$res = $res->toArray();
			$res = array_slice( $res, 0, 8 );
			$res = array_reverse( $res );
			$res = Uint::fromUint8Array( $res )->toHexString();
			
			if( hexToDec( $res ) >= hexToDec( $difficulty ) ) return true;
			
			return false;
		}
	}

?>