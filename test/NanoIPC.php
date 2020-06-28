<?php 

require_once __DIR__ . '/autoload.php';

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['path' => '/tmp/nano']);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanoipc_unix->AccountWeight(['account' => $account]);

var_dump($nanoipc_unix);

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['hostname' => 'localhost', 'port' => 7077]);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanoipc_tcp->AccountWeight(['account' => $account]);

var_dump($nanoipc_tcp);
