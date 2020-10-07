<?php

namespace MikeRow\Bandano;

use \Exception;

class BananoBlockException extends Exception{}

class BananoBlock
{
    // * Owner informations
    
    private $privateKey;
    private $publicKey;
    private $account;
    
    
    // * Block data
    
    private $prevAuto;
    private $prevBlockId;
    private $prevBlock = [];
    private $rawBlockId = [];
    private $signature;
    private $work;
    
    
    // * Results and debug
    
    public $block = [];
    public $blockId;


    // *
    // *  Initialization
    // *
    
    public function __construct(string $private_key)
    {
        if (strlen($private_key) != 64 || !hex2bin($private_key)) {
            throw new BananoBlockException("Invalid private key: $private_key");
        }
        
        $this->privateKey = $private_key;
        $this->publicKey  = BananoTool::private2public($private_key);
        $this->account    = BananoTool::public2account($this->publicKey);
    }
    
    
    // *
    // *  Set previous block
    // *
    
    public function setPrev(string $prev_block_id, array $prev_block)
    {
        if (strlen($prev_block_id) != 64 || !hex2bin($prev_block_id)) {
            throw new BananoBlockException("Invalid previous block ID: $prev_block_id");
        }
        if (count($prev_block) < 8) {
            throw new BananoBlockException("Invalid previous block array count: less than 8");
        }
        
        $this->prevBlockId = $prev_block_id;
        $this->prevBlock   = $prev_block;
    }
    
    
    // *
    // *  Automatically set previous block
    // *
    
    public function autoPrev(bool $auto)
    {
        $this->prevAuto = $auto;
    }
    
    
    // *
    // *  Set work
    // *
    
    public function setWork(string $work)
    {
        if (strlen($work) != 16 || !hex2bin($work)) {
            throw new BananoBlockException("Invalid work: $work");
        }
        
        $this->work = $work;
    }
    
    
    // *
    // *  Build open block
    // *
    
    public function open(string $pairing_block_id, string $received_amount, string $representative): array
    {
        // Check inputs
        if (strlen($pairing_block_id) != 64 || !hex2bin($pairing_block_id)) {
            throw new BananoBlockException("Invalid pairing block ID: $pairing_block_id");
        }
        if (!ctype_digit($received_amount)) {
            throw new BananoBlockException("Invalid received amount: $received_amount");
        }
        if (!BananoTool::account2public($representative, false)) {
            throw new BananoBlockException("Invalid representative: $representative");
        }
        if ($this->work != null) {
            if (strlen($this->work) != 16 || !hex2bin($this->work)) {
                throw new BananoBlockException("Invalid work: {$this->work}");
            }
        }
        
        // Build block
        $balance = dechex($received_amount);
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = BananoTool::PREAMBLE_HEX;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = BananoTool::EMPTY32_HEX;
        $this->rawBlockId[] = BananoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = $pairing_block_id;
        
        $this->blockId   = BananoTool::hashHexs($this->rawBlockId);
        $this->signature = BananoTool::sign($this->blockId, $this->privateKey);
        
        $this->block = [
            'type'           => 'state',
            'account'        => $this->account,
            'previous'       => BananoTool::EMPTY32_HEX,
            'representative' => $representative,
            'balance'        => hexdec($balance),
            'link'           => $pairing_block_id,
            'signature'      => $this->signature,
            'work'           => $this->work
        ];
        
        if ($this->prevAuto) {
            $this->prevBlock   = $this->block;
            $this->prevBlockId = $this->blockId;
        } else {
            $this->prevBlock   = [];
            $this->prevBlockId = null;
        }
        
        $this->work = null;
        
        return $this->block;
    }
    
    
    // *
    // *  Build receive block
    // *
    
    public function receive(string $pairing_block_id, string $received_amount, string $representative = null): array
    {
        // Check previous block info and ID
        if (!isset($this->prevBlock['balance']) ||
            !isset($this->prevBlock['representative']) ||
            !ctype_digit($this->prevBlock['balance']) ||
            !BananoTool::account2public($this->prevBlock['representative'], false)
        ) {
            throw new BananoBlockException("Invalid previous block");
        }
        if (strlen($this->prevBlockId) != 64 || !hex2bin($this->prevBlockId)) {
            throw new BananoBlockException("Invalid previous block ID: {$this->prevBlockId}");
        }
        
        // Check inputs
        if (strlen($pairing_block_id) != 64 || !hex2bin($pairing_block_id)) {
            throw new BananoBlockException("Invalid pairing block ID: $pairing_block_id");
        }
        if (!ctype_digit($received_amount)) {
            throw new BananoBlockException("Invalid received amount: $received_amount");
        }
        if ($representative == null) {
            $representative = $this->prevBlock['representative'];
        }
        if (!BananoTool::account2public($representative, false)) {
            throw new BananoBlockException("Invalid representative: $representative");
        }
        if ($this->work != null) {
            if (strlen($this->work) != 16 || !hex2bin($this->work)) {
                throw new BananoBlockException("Invalid work: {$this->work}");
            }
        }
        
        // Build block
        $balance = dechex(
            gmp_strval(
                gmp_add($this->prevBlock['balance'], $received_amount)
            )
        );
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = BananoTool::PREAMBLE_HEX;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = $this->prevBlockId;
        $this->rawBlockId[] = BananoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = $pairing_block_id;
        
        $this->blockId   = BananoTool::hashHexs($this->rawBlockId);
        $this->signature = BananoTool::sign($this->blockId, $this->privateKey);
        
        $this->block = [
            'type'           => 'state',
            'account'        => $this->account,
            'previous'       => $this->prevBlockId,
            'representative' => $representative,
            'balance'        => hexdec($balance),
            'link'           => $pairing_block_id,
            'signature'      => $this->signature,
            'work'           => $this->work
        ];
        
        if ($this->prevAuto) {
            $this->prevBlock   = $this->block;
            $this->prevBlockId = $this->blockId;
        } else {
            $this->prevBlock   = [];
            $this->prevBlockId = null;
        }

        $this->work = null;
        
        return $this->block;
    }
    
    
    // *
    // *  Build send block
    // *
    
    public function send(string $destination, string $sending_amount, string $representative = null): array
    {
        // Check previous block info and ID
        if (!isset($this->prevBlock['balance']) ||
            !isset($this->prevBlock['representative']) ||
            !ctype_digit($this->prevBlock['balance']) ||
            !BananoTool::account2public($this->prevBlock['representative'], false)
        ) {
            throw new BananoBlockException("Invalid previous block");
        }
        if (strlen($this->prevBlockId) != 64 || !hex2bin($this->prevBlockId)) {
            throw new BananoBlockException("Invalid previous block ID: {$this->prevBlockId}");
        }
                
        // Check inputs
        if (!BananoTool::account2public($destination, false)) {
            throw new BananoBlockException("Invalid destination: $destination");
        }
        if (!ctype_digit($sending_amount)) {
            throw new BananoBlockException("Invalid sending amount: $sending_amount");
        }
        if ($representative == null) {
            $representative = $this->prevBlock['representative'];
        }
        if (!BananoTool::account2public($representative, false)) {
            throw new BananoBlockException("Invalid representative: $representative");
        }
        if ($this->work != null) {
            if (strlen($this->work) != 16 || !hex2bin($this->work)) {
                throw new BananoBlockException("Invalid work: {$this->work}");
            }
        }
        
        // Build block
        $balance = dechex(
            gmp_strval(
                gmp_sub($this->prevBlock['balance'], $sending_amount)
            )
        );
        if (strpos($balance, '-') !== false) {
            throw new BananoBlockException("Insufficient balance: $balance");
        }
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = BananoTool::PREAMBLE_HEX;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = $this->prevBlockId;
        $this->rawBlockId[] = BananoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = BananoTool::account2public($destination);
        
        $this->blockId   = BananoTool::hashHexs($this->rawBlockId);
        $this->signature = BananoTool::sign($this->blockId, $this->privateKey);
        
        $this->block = [
            'type'           => 'state',
            'account'        => $this->account,
            'previous'       => $this->prevBlockId,
            'representative' => $representative,
            'balance'        => hexdec($balance),
            'link'           => $destination,
            'signature'      => $this->signature,
            'work'           => $this->work
        ];
        
        if ($this->prevAuto) {
            $this->prevBlock   = $this->block;
            $this->prevBlockId = $this->blockId;
        } else {
            $this->prevBlock   = [];
            $this->prevBlockId = null;
        }
        
        $this->work = null;
        
        return $this->block;
    }
    
    
    // *
    // *  Build change block
    // *
    
    public function change(string $representative): array
    {
        // Check previous block info and ID
        if (!isset($this->prevBlock['balance']) ||
            !isset($this->prevBlock['representative']) ||
            !ctype_digit($this->prevBlock['balance']) ||
            !BananoTool::account2public($this->prevBlock['representative'], false)
        ) {
            throw new BananoBlockException("Invalid previous block");
        }
        if (strlen($this->prevBlockId) != 64 || !hex2bin($this->prevBlockId)) {
            throw new BananoBlockException("Invalid previous block ID: {$this->prevBlockId}");
        }
                
        // Check inputs
        if (!BananoTool::account2public($representative, false)) {
            throw new BananoBlockException("Invalid representative: $representative");
        }
        if ($this->work != null) {
            if (strlen($this->work) != 16 || !hex2bin($this->work)) {
                throw new BananoBlockException("Invalid work: {$this->work}");
            }
        }
        
        // Build block
        $balance = dechex($this->prevBlock['balance']);
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = BananoTool::PREAMBLE_HEX;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = $this->prevBlockId;
        $this->rawBlockId[] = BananoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = BananoTool::EMPTY32_HEX;
        
        $this->blockId   = BananoTool::hashHexs($this->rawBlockId);
        $this->signature = BananoTool::sign($this->blockId, $this->privateKey);
        
        $this->block = [
            'type'           => 'state',
            'account'        => $this->account,
            'previous'       => $this->prevBlockId,
            'representative' => $representative,
            'balance'        => hexdec($balance),
            'link'           => BananoTool::EMPTY32_HEX,
            'signature'      => $this->signature,
            'work'           => $this->work
        ];
        
        if ($this->prevAuto) {
            $this->prevBlock   = $this->block;
            $this->prevBlockId = $this->blockId;
        } else {
            $this->prevBlock   = [];
            $this->prevBlockId = null;
        }
        
        $this->work = null;
        
        return $this->block;
    }
}
