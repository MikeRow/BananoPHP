<?php 

require __DIR__ . '/../autoload.php';

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$cycles = 10000;


// * API v1

$nanorpc = new MikeRow\NanoPHP\NanoRpc('http', 'localhost', 7076);

$nanorpc->setNanoApi(1);

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $nanorpc->account_weight(['account' => $account]);
}

echo 'Time v1: ' . (microtime(true) - $t0) . PHP_EOL;


// * API v2

$nanorpc = new MikeRow\NanoPHP\NanoRpc('http', 'localhost', 7076, 'api/v2');

$nanorpc->setNanoApi(2);

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $nanorpc->AccountWeight(['account' => $account]);
}

echo 'Time v2: ' . (microtime(true) - $t0) . PHP_EOL;
