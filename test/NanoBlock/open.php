<?php

require __DIR__ . '/../autoload.php';

use MikeRow\Bandano\NanoTool;

// Owner data
$private_key = '';
$public_key  = '';
$account     = '';

// Block data
$open_difficulty  = 'fffffe0000000000';
$pairing_block_id = '';
$received_amount  = '';
$representative   = '';

// Initialize NanoRPC and NanoBlock
$nanorpc    = new MikeRow\Bandano\NanoRPC();
$nanoblock  = new MikeRow\Bandano\NanoBlock($private_key);

// Generate work
$work = NanoTool::work($public_key, $open_difficulty);

// Build block
$nanoblock->setWork($work);
$nanoblock->open($pairing_block_id, $received_amount, $representative);

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
