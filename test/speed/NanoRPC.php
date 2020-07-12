<?php 

require __DIR__ . '/../autoload.php';

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// # API v1

$nanorpc = new mikerow\php4nano\NanoRPC('http', 'localhost', 7076);

$t0 = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $nanorpc->account_weight(['account' => $account]);
}

echo 'Time v1: ' . (microtime(true) - $t0) . PHP_EOL;


// # API v2

$nanorpc = new mikerow\php4nano\NanoRPC('http', 'localhost', 7076, 'api/v2');

$nanorpc->setNanoApi(2);

$t0 = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $nanorpc->AccountWeight(['account' => $account]);
}

echo 'Time v2: ' . (microtime(true) - $t0) . PHP_EOL;
