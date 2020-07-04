<?php 

require_once __DIR__ . '/autoload.php';


// # Unix domain socket encoding 2

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['path_to_socket' => '/tmp/nano']);

$nanoipc_unix->setNanoEncoding(2);

$nanoipc_unix->open();

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_unix->account_weight(['account' => $account]);

echo 'Time unix 2: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_unix);

$nanoipc_unix->close();


// # Unix domain socket encoding 4

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['path_to_socket' => '/tmp/nano']);

$nanoipc_unix->open();

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_unix->AccountWeight(['account' => $account]);

echo 'Time unix 4: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_unix);

$nanoipc_unix->close();


// # TCP encoding 2

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['hostname' => 'localhost', 'port' => 7077]);

$nanoipc_tcp->setNanoEncoding(2);

$nanoipc_tcp->open();

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_tcp->account_weight(['account' => $account]);

echo 'Time TCP 2: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_tcp);

$nanoipc_tcp->close();


// # TCP encoding 4

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['hostname' => 'localhost', 'port' => 7077]);

$nanoipc_tcp->open();

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$t0 = microtime(true);

$nanoipc_tcp->AccountWeight(['account' => $account]);

echo 'Time TCP 4: ' . (microtime(true) - $t0) . PHP_EOL;

var_dump($nanoipc_tcp);

$nanoipc_tcp->close();
