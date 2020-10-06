<?php

require __DIR__ . '/../autoload.php';

use MikeRow\Bandano\BananoTool;

// Owner data
$private_key = '';
$public_key  = '';
$account     = '';

// Block data
$open_difficulty  = 'fffffe0000000000';
$pairing_block_id = '';
$received_amount  = '';
$representative   = '';

// Initialize BananoRPC and BananoBlock
$bananorpc    = new MikeRow\Bandano\BananoRPC();
$bananoblock  = new MikeRow\Bandano\BananoBlock($private_key);

// Generate work
$work = BananoTool::work($public_key, $open_difficulty);

// Build block
$bananoblock->setWork($work);
$bananoblock->open($pairing_block_id, $received_amount, $representative);

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
