<?php

namespace php4nano;

use \Exception as Exception;

class NanoBlock
{
    // # Owner informations
    
    private $privateKey = null;
    private $publicKey  = null;
    private $account    = null;
    
    
    // # Block data
    
    private $prevAuto    = false;
    private $prevBlockId = null;
    private $prevBlock   = [];
    private $rawBlockId  = [];
    private $signature   = null;
    private $work        = null;
    
    
    // # Results and debug
    
    public $blockId = null;
    public $block   = [];


    // #
    // ## Initialization
    // #
    
    public function __construct(string $private_key)
    {
        if (strlen($private_key) != 64 || !hex2bin($private_key)) {
            throw new Exception("Invalid private key: $private_key");
        }
        
        $this->privateKey = $private_key;
        $this->publicKey  = NanoTool::private2public($private_key);
        $this->account    = NanoTool::public2account($this->publicKey);
    }
    
    
    // #
    // ## Set previous block
    // #
    
    public function setPrev(string $prev_block_id, array $prev_block)
    {
        if (strlen($prev_block_id) != 64 || !hex2bin($prev_block_id)) {
            throw new Exception("Invalid previous block ID: $prev_block_id");
        }
        if (count($prev_block) < 8) {
            throw new Exception("Invalid previous block array count: less than 8");
        }
        
        $this->prevBlockId  = $prev_block_id;
        $this->prevBlock = $prev_block;
    }
    
    
    // #
    // ## Automatically set previous block
    // #
    
    public function autoPrev(bool $auto)
    {
        $this->prevAuto = $auto;
    }
    
    
    // #
    // ## Set work
    // #
    
    public function setWork(string $work)
    {
        if (strlen($work) != 16 || !hex2bin($work)) {
            throw new Exception("Invalid work: $work");
        }
        
        $this->work = $work;
    }
    
    
    // #
    // ## Build open block
    // #
    
    public function open(string $pairing_block_id, string $amount, string $representative) : array
    {
        if (strlen($pairing_block_id) != 64 || !hex2bin($pairing_block_id)) {
            throw new Exception("Invalid previous block ID: $pairing_block_id");
        }
        if (!ctype_digit($amount)) {
            throw new Exception("Invalid amount: $amount");
        }
        if (!NanoTool::account2public($representative, false)) {
            throw new Exception("Invalid representative: $representative");
        }
        
        $balance = dechex($amount);
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = NanoTool::PREAMBLE;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = NanoTool::EMPTY32;
        $this->rawBlockId[] = NanoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = $pairing_block_id;
        
        $this->blockId   = NanoTool::hashHexs($this->rawBlockId);
        $this->signature = NanoTool::sign($this->blockId, $this->privateKey);
        
        $this->block = [
            'type'           => 'state',
            'account'        => $this->account,
            'previous'       => NanoTool::EMPTY32,
            'representative' => $representative,
            'balance'        => hexdec($balance),
            'link'           => $pairing_block_id,
            'signature'      => $this->signature,
            'work'           => $this->work
        ];
        
        if ($this->prevAuto) {
            $this->prevBlockId = $this->blockId;
            $this->prevBlock   = $this->block;
        }
        
        return $this->block;
    }
    
    
    // #
    // ## Build receive block
    // #
    
    public function receive(string $pairing_block_id, string $amount, string $representative = null) : array
    {
        if (strlen($pairing_block_id) != 64 || !hex2bin($pairing_block_id)) {
            throw new Exception("Invalid previous block ID: $pairing_block_id");
        }
        if (!ctype_digit($amount)) {
            throw new Exception("Invalid amount: $amount");
        }
        if ($representative == null) {
            $representative = $this->prevBlock['representative'];
        }
        if (!NanoTool::account2public($representative, false)) {
            throw new Exception("Invalid representative: $representative");
        }
        
        $balance = dechex(gmp_strval(gmp_add(hexdec($this->prevBlock['balance']), $amount)));
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = NanoTool::PREAMBLE;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = $this->prevBlockId;
        $this->rawBlockId[] = NanoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = $pairing_block_id;
        
        $this->blockId   = NanoTool::hashHexs($this->rawBlockId);
        $this->signature = NanoTool::sign($this->blockId, $this->privateKey);
        
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
            $this->prevBlockId = $this->blockId;
            $this->prevBlock   = $this->block;
        }
        
        return $this->block;
    }
    
    
    // #
    // ## Build send block
    // #
    
    public function send(string $destination, string $amount, string $representative = null) : array
    {
        if (!NanoTool::account2public($destination, false)) {
            throw new Exception("Invalid destination: $destination");
        }
        if (!ctype_digit($amount)) {
            throw new Exception("Invalid amount: $amount");
        }
        if ($representative == null) {
            $representative = $this->prevBlock['representative'];
        }
        if (!NanoTool::account2public($representative, false)) {
            throw new Exception("Invalid representative: $representative");
        }
        
        $balance = dechex(
            gmp_strval(
                gmp_sub(hexdec($this->prev_block['balance']), $amount)
            )
        );
        if (strpos($balance, '-') !== false) {
            throw new Exception("Insufficient balance: $balance");
        }
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = NanoTool::PREAMBLE;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = $this->prevBlockId;
        $this->rawBlockId[] = NanoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = NanoTool::account2public($destination);
        
        $this->blockId   = NanoTool::hashHexs($this->rawBlockId);
        $this->signature = NanoTool::sign($this->blockId, $this->privateKey);
        
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
            $this->prevBlockId = $this->blockId;
            $this->prevBlock   = $this->block;
        }
        
        return $this->block;
    }
    
    
    // #
    // ## Build change block
    // #
    
    public function change(string $representative) : array
    {
        if (!NanoTool::account2public($representative, false)) {
            throw new Exception("Invalid representative: $representative");
        }
        
        $balance = dechex($this->prevBlock['balance']);
        $balance = str_repeat('0', (32 - strlen($balance))) . $balance;
        
        $this->rawBlockId   = [];
        $this->rawBlockId[] = NanoTool::PREAMBLE;
        $this->rawBlockId[] = $this->publicKey;
        $this->rawBlockId[] = $this->prevBlockId;
        $this->rawBlockId[] = NanoTool::account2public($representative);
        $this->rawBlockId[] = $balance;
        $this->rawBlockId[] = NanoTool::EMPTY32;
        
        $this->blockId   = NanoTool::hashHexs($this->rawBlockId);
        $this->signature = NanoTool::sign($this->blockId, $this->privateKey);
        
        $this->block = [
            'type'           => 'state',
            'account'        => $this->account,
            'previous'       => $this->prevBlockId,
            'representative' => $representative,
            'balance'        => hexdec($balance),
            'link'           => NanoTool::EMPTY32,
            'signature'      => $this->signature,
            'work'           => $this->work
        ];
        
        if ($this->prevAuto) {
            $this->prevBlockId = $this->blockId;
            $this->prevBlock   = $this->block;
        }
        
        return $this->block;
    }
}
