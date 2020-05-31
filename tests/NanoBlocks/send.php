<?php

require_once __DIR__ . '/../../lib/NanoTools.php';
require_once __DIR__ . '/../../lib/NanoBlocks.php';
require_once __DIR__ . '/../../lib/NanoRPCExt.php';

use php4nano\NanoTools as NanoTools;

$nanorpc = new php4nano\NanoRPCExt();

$private_key    = ''; // Owner account secret key
$public_key     = ''; // Owner account public key
$account        = ''; // Owner account

$difficulty   = 'ffffffc000000000'; // Current send difficulty
$account_info = $nanorpc->account_info(['account' => $account]);
$block_info   = $nanorpc->block_info(['json_block' => true, 'hash' => $account_info['frontier']]);

$work = NanoTools::getWork($account_info['frontier'], $difficulty);

$nanoblocks = new php4nano\NanoBlocks($private_key);

$nanoblocks->setPrev($account_info['frontier'], $block_info['contents']);
$nanoblocks->setWork($work);
$nanoblocks->send('', '');

$open = $nanorpc->process(['json_block' => 'true', 'block' => $nanoblocks->block]);

if ($nanorpc->error) {
    echo $nanorpc->error . PHP_EOL;
}

var_dump($open);
