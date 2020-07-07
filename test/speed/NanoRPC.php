<?php 

require_once __DIR__ . '/../autoload.php';


// # API v1

$nanorpc = new php4nano\NanoRPC('http', 'localhost', 7076);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanorpc->account_weight(['account' => $account]);
}

echo 'Time v1: ' . (microtime(true) - $t0) . PHP_EOL;


// # API v2

$nanorpc = new php4nano\NanoRPC('http', 'localhost', 7076, 'api/v2');

$nanorpc->setNanoApi(2);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanorpc->AccountWeight(['account' => $account]);
}

echo 'Time v2: ' . (microtime(true) - $t0) . PHP_EOL;
