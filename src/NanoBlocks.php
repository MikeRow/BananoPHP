<?php

    namespace php4nano\NanoBlocks;
    
    require_once __DIR__ . '/NanoTools.php';
    
    use \Exception as Exception;
    use php4nano\NanoTools\NanoTools as NanoTools;
    
    class NanoBlocks
    {
        // # Owner informations
        
        private $privateKey = null;
        private $publicKey  = null;
        private $account    = null;
        
        
        // # Block data
        
        private $prevAuto    = false;
        private $prevBlockID = null;
        private $prevBlock   = [];
        private $rawBlockID  = [];
        private $signature   = null;
        private $work        = null;
        
        
        // # Results and debug
        
        public $blockID = null;
        public $block   = [];


        // #
        // ## Initialization
        // #
        
        public function __construct( string $sk )
        {
            if( strlen( $sk ) != 64 || !hex2bin( $sk ) ) throw new Exception( "Invalid private key: $sk" );
            
            $this->privateKey = $private_key;
            $this->publicKey  = NanoTools::private2public( $sk );
            $this->account    = NanoTools::public2account( $this->publicKey );
        }
        
        
        // #
        // ## Set previous block
        // #
        
        public function setPrev( string $prev_block_id, array $prev_block )
        {
            if( strlen( $prev_block_id ) != 64 || !hex2bin( $prev_block_id ) ) throw new Exception( "Invalid block ID: $prev_block_id" );
            if( count( $prev_block ) < 8 ) throw new Exception( "Block array count is less than 8" );
            
            $this->prevBlockID  = $prev_block_id;
            $this->prevBlock = $prev_block;
        }
        
        
        // #
        // ## Automatically set previous block
        // #
        
        public function autoPrev( bool $auto )
        {
            $this->prevAuto = $auto;
        }
        
        
        // #
        // ## Set work
        // #
        
        public function setWork( string $work )
        {
            if( strlen( $work ) != 16 || !hex2bin( $work ) ) throw new Exception( "Invalid work: $work" );
            
            $this->work = $work;
        }
        
        
        // #
        // ## Build open block
        // #
        
        public function open( string $pairing_block_id, string $amount, string $representative )
        {
            if( strlen( $pairing_block_id ) != 64 || !hex2bin( $pairing_block_id ) ) throw new Exception( "Invalid previous block ID: $pairing_block_id" );
            if( !ctype_digit( $amount ) ) throw new Exception( "Invalid raw amount: $amount" );
            if( !NanoTools::account2public( $representative, false ) ) throw new Exception( "Invalid representative account: $representative" );
            
            $balance = NanoTools::dec2hex( $amount );
            $balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
            
            $this->rawBlockID   = [];
            $this->rawBlockID[] = NanoTools::PREAMBLE;
            $this->rawBlockID[] = $this->publicKey;
            $this->rawBlockID[] = NanoTools::EMPTY32;
            $this->rawBlockID[] = NanoTools::account2public( $representative );
            $this->rawBlockID[] = $balance;
            $this->rawBlockID[] = $pairing_block_id;
            
            $this->blockID   = NanoTools::getBlockID( $this->rawBlockID );
            $this->signature = NanoTools::signMessage( $this->privateKey, $this->blockID );
            
            $this->block = 
            [
                'type'           => 'state',
                'account'        => $this->account,
                'previous'       => NanoTools::EMPTY32,
                'representative' => $representative,
                'balance'        => NanoTools::hex2dec( $balance ),
                'link'           => $pairing_block_id,
                'signature'      => $this->signature,
                'work'           => $this->work
            ];
            
            if( $this->prevAuto )
            {
                $this->prevBlockID = $this->blockID;
                $this->prevBlock   = $this->block;
            }
            
            return $this->block;
        }
        
        
        // #
        // ## Build receive block
        // #
        
        public function receive( string $pairing_block_id, string $amount, string $representative = null )
        {
            if( strlen( $pairing_block_id ) != 64 || !hex2bin( $pairing_block_id ) ) throw new Exception( "Invalid previous block ID: $pairing_block_id" );
            if( !ctype_digit( $amount ) ) throw new Exception( "Invalid raw amount: $amount" );
            if( $representative == null ) $representative = $this->prevBlock['representative'];
            if( !NanoTools::account2public( $representative, false ) ) throw new Exception( "Invalid representative account: $representative" );
            
            $balance  = NanoTools::dec2hex( gmp_strval( gmp_add( NanoTools::hex2dec( $this->prevBlock['balance'] ), $amount ) ) );
            $balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
            
            $this->rawBlockID   = [];
            $this->rawBlockID[] = NanoTools::PREAMBLE;
            $this->rawBlockID[] = $this->publicKey;
            $this->rawBlockID[] = $this->prevBlockID;
            $this->rawBlockID[] = NanoTools::account2public( $representative );
            $this->rawBlockID[] = $balance;
            $this->rawBlockID[] = $pairing_block_id;
            
            $this->blockID   = NanoTools::getBlockID( $this->rawBlockID );
            $this->signature = NanoTools::signMessage( $this->privateKey, $this->blockID );
            
            $this->block = 
            [
                'type'           => 'state',
                'account'        => $this->account,
                'previous'       => $this->prevBlockID,
                'representative' => $representative,
                'balance'        => NanoTools::hex2dec( $balance ),
                'link'           => $pairing_block_id,
                'signature'      => $this->signature,
                'work'           => $this->work
            ];
            
            if( $this->prevAuto )
            {
                $this->prevBlockID = $this->blockID;
                $this->prevBlock   = $this->block;
            }
            
            return $this->block;
        }
        
        
        // #
        // ## Build send block
        // #
        
        public function send( string $destination, string $amount, string $representative = null )
        {
            if( !NanoTools::account2public( $destination, false ) ) throw new Exception( "Invalid destination account: $representative" );
            if( !ctype_digit( $amount ) ) throw new Exception( "Invalid raw amount: $amount" );
            if( $representative == null ) $representative = $this->prevBlock['representative'];
            if( !NanoTools::account2public( $representative, false ) ) throw new Exception( "Invalid representative account: $representative" );
            
            $balance  = NanoTools::dec2hex( gmp_strval( gmp_sub( NanoTools::hex2dec( $this->prev_block['balance'] ), $amount ) ) );
            if( strpos( $balance, '-' ) !== false ) throw new Exception( "Insufficient balance: $balance" );
            $balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
            
            $this->rawBlockID   = [];
            $this->rawBlockID[] = NanoTools::PREAMBLE;
            $this->rawBlockID[] = $this->publicKey;
            $this->rawBlockID[] = $this->prevBlockID;
            $this->rawBlockID[] = NanoTools::account2public( $representative );
            $this->rawBlockID[] = $balance;
            $this->rawBlockID[] = NanoTools::account2public( $destination );
            
            $this->blockID   = NanoTools::getBlockID( $this->rawBlockID );
            $this->signature = NanoTools::signMessage( $this->privateKey, $this->blockID );
            
            $this->block =
            [
                'type'           => 'state',
                'account'        => $this->account,
                'previous'       => $this->prevBlockID,
                'representative' => $representative,
                'balance'        => NanoTools::hex2dec( $balance ),
                'link'           => $destination,
                'signature'      => $this->signature,
                'work'           => $this->work
            ];
            
            if( $this->prevAuto )
            {
                $this->prevBlockID = $this->blockID;
                $this->prevBlock   = $this->block;
            }
            
            return $this->block;
        }
        
        
        // #
        // ## Build change block
        // #
        
        public function change( string $representative )
        {
            if( !NanoTools::account2public( $representative, false ) ) throw new Exception( "Invalid representative account: $representative" );
            
            $balance = NanoTools::dec2hex( $this->prevBlock['balance'] );
            $balance = str_repeat( '0', ( 32 - strlen( $balance ) ) ) . $balance;
            
            $this->rawBlockID   = [];
            $this->rawBlockID[] = NanoTools::PREAMBLE;
            $this->rawBlockID[] = $this->publicKey;
            $this->rawBlockID[] = $this->prevBlockID;
            $this->rawBlockID[] = NanoTools::account2public( $representative );
            $this->rawBlockID[] = $balance;
            $this->rawBlockID[] = NanoTools::EMPTY32;
            
            $this->blockID   = NanoTools::getBlockID( $this->rawBlockID );
            $this->signature = NanoTools::signMessage( $this->privateKey, $this->blockID );
            
            $this->block =
            [
                'type'           => 'state',
                'account'        => $this->account,
                'previous'       => $this->prevBlockID,
                'representative' => $representative,
                'balance'        => NanoTools::hex2dec( $balance ),
                'link'           => NanoTools::EMPTY32,
                'signature'      => $this->signature,
                'work'           => $this->work
            ];
            
            if( $this->prevAuto )
            {
                $this->prevBlockID = $this->blockID;
                $this->prevBlock   = $this->block;
            }
            
            return $this->block;
        }
    }
    