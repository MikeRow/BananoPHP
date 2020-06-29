<?php 

require_once __DIR__ . '/autoload.php';

$nanorpc2 = new php4nano\NanoRPC2();

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanorpc2->AccountWeight(['account' => $account]);

echo 'Time: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanorpc2);
