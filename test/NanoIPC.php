<?php 

require_once __DIR__ . '/autoload.php';

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['path_to_socket' => '/tmp/nano']);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_unix->AccountWeight(['account' => $account]);

echo 'Time unix: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_unix);

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['hostname' => 'localhost', 'port' => 7077]);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_tcp->AccountWeight(['account' => $account]);

echo 'Time TCP: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_tcp);
