<?php 

require_once __DIR__ . '/autoload.php';


// # API v1

$nanorpc = new php4nano\NanoRPC('localhost', 7076);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanorpc->account_weight(['account' => $account]);

echo 'Time v1: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanorpc);


// # API v2

$nanorpc = new php4nano\NanoRPC('localhost', 7076, 'api/v2');

$nanorpc->setAPI(2);

$t0 = microtime(true);

$nanorpc->AccountWeight(['account' => $account]);

echo 'Time v2: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanorpc);
