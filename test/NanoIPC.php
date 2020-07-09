<?php 

require_once __DIR__ . '/autoload.php';

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// # Unix domain socket encoding 2

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['/tmp/nano']);

$nanoipc_unix->setNanoEncoding(2);

$nanoipc_unix->open();

$nanoipc_unix->account_weight(['account' => $account]);

var_dump($nanoipc_unix);

$nanoipc_unix->close();


// # Unix domain socket encoding 4

$nanoipc_unix = new php4nano\NanoIPC('unix_domain_socket', ['/tmp/nano']);

$nanoipc_unix->open();

$nanoipc_unix->AccountWeight(['account' => $account]);

var_dump($nanoipc_unix);

$nanoipc_unix->close();


// # TCP encoding 2

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['localhost', 7077]);

$nanoipc_tcp->setNanoEncoding(2);

$nanoipc_tcp->open();

$nanoipc_tcp->account_weight(['account' => $account]);

var_dump($nanoipc_tcp);

$nanoipc_tcp->close();


// # TCP encoding 4

$nanoipc_tcp = new php4nano\NanoIPC('TCP', ['localhost', 7077]);

$nanoipc_tcp->open();

$nanoipc_tcp->AccountWeight(['account' => $account]);

var_dump($nanoipc_tcp);

$nanoipc_tcp->close();
