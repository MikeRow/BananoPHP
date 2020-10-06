<?php

require __DIR__ . '/../autoload.php';

use MikeRow\Bandano\BananoTool;

// Owner data
$private_key = '';
$public_key  = '';
$account     = '';

// Block data
$send_difficulty = 'fffffff800000000';
$destination     = '';
$sending_amount  = '';
$representative  = '';

// Initialize BananoRPC and BananoBlock
$bananorpc    = new MikeRow\Bandano\BananoRPC();
$bananoblock  = new MikeRow\Bandano\BananoBlock($private_key);

// Get previous block data
$account_info = $bananorpc->account_info(['account' => $account]);
$block_info   = $bananorpc->block_info([
    'json_block' => true,
    'hash' => $account_info['frontier']
]);

// Generate work
$work = BananoTool::work($account_info['frontier'], $send_difficulty);

// Build block
$bananoblock->setPrev($account_info['frontier'], $block_info['contents']);
$bananoblock->setWork($work);
$bananoblock->send($destination, $sending_amount, $representative);

// Publish block
$process = $bananorpc->process([
    'json_block' => 'true',
    'block' => $bananoblock->block
]);

// Results and debug
if ($bananorpc->error) {
    echo $bananorpc->error . PHP_EOL;
}

var_dump($process);
