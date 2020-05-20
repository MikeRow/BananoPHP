<?php

	namespace php4nano\lib\NanoBlocks;
	
	require_once __DIR__ . '/NanoTools.php';
	
	use php4nano\lib\NanoTools\NanoTools as NanoTools;
	
	class NanoBlocks
	{
		// *****************
		// *** Variables ***
		// *****************
		
		
		
		private $private_key;
		private $public_key;
		private $owner;
		
		private $representative;
		private $work;
		
		private $signature;
		private $hash;
		private $raw_signature;
		private $block;
		
		
		
		// *******************************
		// *** Set shared informations ***
		// *******************************
		
		
		
		public function __construct( string $private_key )
		{
			$this->private_key = $private_key;
			$this->public_key  = NanoTools::private2public( $private_key );
			$this->owner       = NanoTools::public2account( $this->public_key );
		}
		
		
		
		// ********************************
		// *** Set block representative ***
		// ********************************
		
		
		
		public function representative_set( $representative )
		{
			$this->representative = $representative;
		}
		
		
		
		// ****************
		// *** Set work ***
		// ****************
		
		
		
		public function work_set( $work )
		{
			$this->work = $work;
		}
		
		
		
		// ****************
		// *** Get hash ***
		// ****************
		
		
		
		public function hash_get()
		{
			return $this->hash;
		}
		
		
		
		// ******************
		// *** Open block ***
		// ******************
		
		
		
		public function open( $pairing_block, $balance )
		{
			$this->raw_signature   = [];
			$this->raw_signature[] = NanoTools::preamble;
			$this->raw_signature[] = $this->public_key;
			$this->raw_signature[] = NanoTools::empty32;
			$this->raw_signature[] = NanoTools::account2public( $this->representative );
			$this->raw_signature[] = $balance;
			$this->raw_signature[] = $pairing_block;
			
			$this->hash      = NanoTools::hash_array( $this->raw_signature, 32 );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block = 
			[
				'type'           => 'state',
				'account'        => $this->owner,
				'previous'       => NanoTools::empty32,
				'representative' => $this->representative,
				'balance'        => NanoTools::str_hex2dec( $balance ),
				'link'           => $pairing_block,
				'signature'      => $this->signature,
				'work'           => $this->work
			];
			
			return $this->block;
		}
		
		
		
		// *********************
		// *** Receive block ***
		// *********************
		
		
		
		public function receive( $previous_block, $pairing_block, $balance )
		{
			$this->raw_signature   = [];
			$this->raw_signature[] = NanoTools::preamble;
			$this->raw_signature[] = $this->public_key;
			$this->raw_signature[] = $previous_block;
			$this->raw_signature[] = NanoTools::account2public( $this->representative );
			$this->raw_signature[] = $balance;
			$this->raw_signature[] = $pairing_block;
			
			$this->hash      = NanoTools::hash_array( $this->raw_signature, 32 );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block =
			[
					'type'           => 'state',
					'account'        => $this->owner,
					'previous'       => $previous_block,
					'representative' => $this->representative,
					'balance'        => NanoTools::str_hex2dec( $balance ),
					'link'           => $pairing_block,
					'signature'      => $this->signature,
					'work'           => $this->work
			];
			
			return $this->block;
		}
		
		
		
		// ******************
		// *** Send block ***
		// ******************
		
		
		
		public function send( $previous_block, $destination, $balance )
		{
			$this->raw_signature   = [];
			$this->raw_signature[] = NanoTools::preamble;
			$this->raw_signature[] = $this->public_key;
			$this->raw_signature[] = $previous_block;
			$this->raw_signature[] = NanoTools::account2public( $this->representative );
			$this->raw_signature[] = $balance;
			$this->raw_signature[] = NanoTools::account2public( $destination );
			
			$this->hash      = NanoTools::hash_array( $this->raw_signature, 32 );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block =
			[
					'type'           => 'state',
					'account'        => $this->owner,
					'previous'       => $previous_block,
					'representative' => $this->representative,
					'balance'        => NanoTools::str_hex2dec( $balance ),
					'link'           => $destination,
					'signature'      => $this->signature,
					'work'           => $this->work
			];
			
			return $this->block;
		}
		
		
		
		// ********************
		// *** Change block ***
		// ********************
		
		
		
		public function change( $previous_block, $balance )
		{
			$this->raw_signature   = [];
			$this->raw_signature[] = NanoTools::preamble;
			$this->raw_signature[] = $this->public_key;
			$this->raw_signature[] = $previous_block;
			$this->raw_signature[] = NanoTools::account2public( $this->representative );
			$this->raw_signature[] = $balance;
			$this->raw_signature[] = NanoTools::empty32;
			
			$this->hash      = NanoTools::hash_array( $this->raw_signature, 32 );
			$this->signature = NanoTools::sign( $this->private_key, $this->hash );
			
			$this->block =
			[
					'type'           => 'state',
					'account'        => $this->owner,
					'previous'       => $previous_block,
					'representative' => $this->representative,
					'balance'        => NanoTools::str_hex2dec( $balance ),
					'link'           => NanoTools::empty32,
					'signature'      => $this->signature,
					'work'           => $this->work
			];
			
			return $this->block;
		}
	}
	
?>