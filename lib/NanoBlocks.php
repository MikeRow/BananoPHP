<?php

	namespace php4nano\lib\NanoBlocks;
	
	require_once __DIR__ . '/NanoTools.php';
	
	use php4nano\lib\NanoTools\NanoTools as NanoTools;
	
	class NanoBlocks
	{
		// *** Configuration options ***
		
		
		private $private_key;
		private $public_key;
		private $account;
		
		private $prev_auto = false;
		private $prev_hash;
		private $prev_block = [];
		private $raw_signature = [];
		private $signature;
		private $work;
		
		public $hash;
		public $block = [];


		// *** Set owner ***
		
		
		public function __construct( string $private_key )
		{
			if( strlen( $private_key ) != 64 || !hex2bin( $private_key ) ) return false;
			
			$this->private_key = $private_key;
			$this->public_key  = NanoTools::private2public( $private_key );
			$this->account     = NanoTools::public2account( $this->public_key );
			
			return true;
		}
		
		
		// *** Set prev (head) block ***
		
		
		public function prev_set( string $prev_hash, array $prev_block )
		{
			if( strlen( $prev_hash ) != 64 || !hex2bin( $prev_hash ) ) return false;
			if( count( $prev_block ) < 8 ) return false;
			
			$this->prev_hash  = $prev_hash;
			$this->prev_block = $prev_block;
			
			return true;
		}
		
		
		// *** Auto-set prev (head) block ***
		
		
		public function prev_auto( bool $auto )
		{
			$this->prev_auto = $auto;
			
			return true;
		}
		
		
		// *** Set work ***	
		
		
		public function work_set( string $work )
		{
			if( strlen( $work ) != 16 || !hex2bin( $work ) ) return false;
			
			$this->work = $work;
			
			return true;
		}
		
		
		
		// ******************
		// *** Open block ***
		// ******************
		
		
		
		public function open( string $pairing_hash, string $amount, string $representative )
		{
			if( strlen( $pairing_hash ) != 64 || !hex2bin( $pairing_hash ) ) return false;
			if( !ctype_digit( $amount ) ) return false;
			if( !NanoTools::account2public( $representative, false ) ) return false;
			
			$balance = NanoTools::str_dec2hex( $amount );
			$balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
			
			$this->raw_block_id   = [];
			$this->raw_block_id[] = NanoTools::preamble;
			$this->raw_block_id[] = $this->public_key;
			$this->raw_block_id[] = NanoTools::empty32;
			$this->raw_block_id[] = NanoTools::account2public( $representative );
			$this->raw_block_id[] = $balance;
			$this->raw_block_id[] = $pairing_hash;
			
			$this->hash      = NanoTools::block_id( $this->raw_block_id );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block = 
			[
				'type'           => 'state',
				'account'        => $this->account,
				'previous'       => NanoTools::empty32,
				'representative' => $representative,
				'balance'        => NanoTools::str_hex2dec( $balance ),
				'link'           => $pairing_hash,
				'signature'      => $this->signature,
				'work'           => $this->work
			];
			
			if( $this->prev_auto )
			{
				$this->prev_hash  = $this->hash;
				$this->prev_block = $this->block;
			}
			
			return $this->block;
		}
		
		
		
		// *********************
		// *** Receive block ***
		// *********************
		
		
		
		public function receive( string $pairing_hash, string $amount, string $representative = null )
		{
			if( strlen( $pairing_hash ) != 64 || !hex2bin( $pairing_hash ) ) return false;
			if( !ctype_digit( $amount ) ) return false;
			if( !NanoTools::account2public( $representative, false ) ) return false;
			
			$balance  = NanoTools::str_dec2hex( gmp_strval( gmp_add( NanoTools::str_hex2dec( $this->prev_block['balance'] ), $amount ) ) );
			$balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
			if( $representative == null ) $representative = $this->prev_block['representative'];
			
			$this->raw_block_id   = [];
			$this->raw_block_id[] = NanoTools::preamble;
			$this->raw_block_id[] = $this->public_key;
			$this->raw_block_id[] = $this->prev_hash;
			$this->raw_block_id[] = NanoTools::account2public( $representative );
			$this->raw_block_id[] = $balance;
			$this->raw_block_id[] = $pairing_hash;
			
			$this->hash      = NanoTools::block_id( $this->raw_block_id );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block = 
			[
				'type'           => 'state',
				'account'        => $this->account,
				'previous'       => $this->prev_hash,
				'representative' => $representative,
				'balance'        => NanoTools::str_hex2dec( $balance ),
				'link'           => $pairing_hash,
				'signature'      => $this->signature,
				'work'           => $this->work
			];
			
			if( $this->prev_auto )
			{
				$this->prev_hash  = $this->hash;
				$this->prev_block = $this->block;
			}
			
			return $this->block;
		}
		
		
		
		// ******************
		// *** Send block ***
		// ******************
		
		
		
		public function send( string $destination, string $amount, string $representative = null )
		{
			if( !NanoTools::account2public( $destination, false ) ) return false;
			if( !ctype_digit( $amount ) ) return false;
			if( !NanoTools::account2public( $representative, false ) ) return false;
			
			$balance  = NanoTools::str_dec2hex( gmp_strval( gmp_sub( NanoTools::str_hex2dec( $this->prev_block['balance'] ), $amount ) ) );
			if( strpos( $balance, '-' ) !== false ) return false;
			$balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
			if( $representative == null ) $representative = $this->prev_block['representative'];
			
			$this->raw_block_id   = [];
			$this->raw_block_id[] = NanoTools::preamble;
			$this->raw_block_id[] = $this->public_key;
			$this->raw_block_id[] = $this->prev_hash;
			$this->raw_block_id[] = NanoTools::account2public( $representative );
			$this->raw_block_id[] = $balance;
			$this->raw_block_id[] = NanoTools::account2public( $destination );
			
			$this->hash      = NanoTools::block_id( $this->raw_block_id );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block =
			[
				'type'           => 'state',
				'account'        => $this->account,
				'previous'       => $this->prev_hash,
				'representative' => $representative,
				'balance'        => NanoTools::str_hex2dec( $balance ),
				'link'           => $destination,
				'signature'      => $this->signature,
				'work'           => $this->work
			];
			
			if( $this->prev_auto )
			{
				$this->prev_hash  = $this->hash;
				$this->prev_block = $this->block;
			}
			
			return $this->block;
		}
		
		
		
		// ********************
		// *** Change block ***
		// ********************
		
		
		
		public function change( string $representative )
		{
			if( !NanoTools::account2public( $representative, false ) ) return false;
			
			$balance = NanoTools::str_dec2hex( $this->prev_block['balance'] );
			$balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
			
			$this->raw_block_id   = [];
			$this->raw_block_id[] = NanoTools::preamble;
			$this->raw_block_id[] = $this->public_key;
			$this->raw_block_id[] = $this->prev_hash;
			$this->raw_block_id[] = NanoTools::account2public( $representative );
			$this->raw_block_id[] = $balance;
			$this->raw_block_id[] = NanoTools::empty32;
			
			$this->hash      = NanoTools::block_id( $this->raw_block_id );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block =
			[
				'type'           => 'state',
				'account'        => $this->account,
				'previous'       => $this->prev_hash,
				'representative' => $representative,
				'balance'        => NanoTools::str_hex2dec( $balance ),
				'link'           => NanoTools::empty32,
				'signature'      => $this->signature,
				'work'           => $this->work
			];
			
			if( $this->prev_auto )
			{
				$this->prev_hash  = $this->hash;
				$this->prev_block = $this->block;
			}
			
			return $this->block;
		}
	}
	
?>