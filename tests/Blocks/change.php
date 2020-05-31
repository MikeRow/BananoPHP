<?php

require_once __DIR__ . '/../../src/Tools.php';
require_once __DIR__ . '/../../src/Blocks.php';
require_once __DIR__ . '/../../src/RPCExt.php';

use php4nano\Tools as NanoTools;

$nanorpc = new php4nano\RPCExt();

$private_key    = ''; // Owner account secret key
$public_key     = ''; // Owner account public key
$account        = ''; // Owner account

$difficulty   = 'ffffffc000000000'; // Current receive difficulty
$account_info = $nanorpc->account_info(['account' => $account]);
$block_info   = $nanorpc->block_info(['json_block' => true, 'hash' => $account_info['frontier']]);

$work = NanoTools::getWork($account_info['frontier'], $difficulty);

$nanoblocks = new php4nano\Blocks($private_key);

$nanoblocks->setPrev($account_info['frontier'], $block_info['contents']);
$nanoblocks->setWork($work);
$nanoblocks->change('');

$open = $nanorpc->process(['json_block' => 'true', 'block' => $nanoblocks->block]);

if ($nanorpc->error) {
	echo $nanorpc->error . PHP_EOL;
}

print_r($open);
