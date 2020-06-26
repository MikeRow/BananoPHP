<?php

require_once __DIR__ . '/../autoload.php';

use php4nano\NanoTool;

// Owner data
$private_key = '';
$public_key  = '';
$account     = '';

// Block data
$receive_difficulty = 'fffffe0000000000';
$pairing_block_id   = '';
$received_amount    = '';
$representative     = '';

// Initialize NanoRPC and NanoBlock
$nanorpc   = new php4nano\NanoRPC();
$nanoblock = new php4nano\NanoBlock($private_key);

// Get previous block data
$account_info = $nanorpc->account_info(['account' => $account]);
$block_info   = $nanorpc->block_info([
    'json_block' => true,
    'hash' => $account_info['frontier']
]);

// Generate work
$work = NanoTool::work($account_info['frontier'], $receive_difficulty);

// Build block
$nanoblock->setPrev($account_info['frontier'], $block_info['contents']);
$nanoblock->setWork($work);
$nanoblock->receive($pairing_block_id, $received_amount, $representative);

// Publish block
$process = $nanorpc->process([
    'json_block' => 'true',
    'block' => $nanoblock->block
]);

// Results and debug
if ($nanorpc->error) {
    echo $nanorpc->error . PHP_EOL;
}

var_dump($process);
