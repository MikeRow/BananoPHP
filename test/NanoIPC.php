<?php 

require_once __DIR__ . '/autoload.php';


// # Unix domain socket encoding 2

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['path_to_socket' => '/tmp/nano']);

$nanoipc_unix->setEncoding(2);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_unix->account_weight(['account' => $account]);

echo 'Time unix 2: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_unix);


// # Unix domain socket encoding 4

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['path_to_socket' => '/tmp/nano']);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_unix->AccountWeight(['account' => $account]);

echo 'Time unix 4: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_unix);


// # TCP encoding 2

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['hostname' => 'localhost', 'port' => 7077]);

$nanoipc_tcp->setEncoding(2);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_tcp->account_weight(['account' => $account]);

echo 'Time TCP 2: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_tcp);


// # TCP encoding 4

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['hostname' => 'localhost', 'port' => 7077]);

$nanoipc_tcp->setEncoding(4);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_tcp->AccountWeight(['account' => $account]);

echo 'Time TCP 4: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_tcp);
