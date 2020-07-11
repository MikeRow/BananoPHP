<?php 

require_once __DIR__ . '/../autoload.php';

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// # Unix domain socket encoding 2

$nanoipc_unix = new php4nano\NanoIPC('unix', ['/tmp/nano']);

$nanoipc_unix->setNanoEncoding(2);

$nanoipc_unix->open();

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanoipc_unix->account_weight(['account' => $account]);
}

echo 'Time unix enc 2: ' . (microtime(true) - $t0) . PHP_EOL;

$nanoipc_unix->close();


// # Unix domain socket encoding 3

$nanoipc_unix = new php4nano\NanoIPC('unix', ['/tmp/nano']);

$nanoipc_unix->setNanoEncoding(3);

$nanoipc_unix->open();

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanoipc_unix->AccountWeight(['Account' => $account]);
}

echo 'Time unix enc 3: ' . (microtime(true) - $t0) . PHP_EOL;

$nanoipc_unix->close();


// # Unix domain socket encoding 4

$nanoipc_unix = new php4nano\NanoIPC('unix', ['/tmp/nano']);

$nanoipc_unix->open();

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanoipc_unix->AccountWeight(['account' => $account]);
}

echo 'Time unix enc 4: ' . (microtime(true) - $t0) . PHP_EOL;

$nanoipc_unix->close();


// # TCP encoding 2

$nanoipc_tcp = new php4nano\NanoIPC('tcp', ['localhost', 7077]);

$nanoipc_tcp->setNanoEncoding(2);

$nanoipc_tcp->open();

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanoipc_tcp->account_weight(['account' => $account]);
}

echo 'Time TCP enc 2: ' . (microtime(true) - $t0) . PHP_EOL;

$nanoipc_tcp->close();


// # TCP encoding 3

$nanoipc_tcp = new php4nano\NanoIPC('tcp', ['localhost', 7077]);

$nanoipc_tcp->setNanoEncoding(3);

$nanoipc_tcp->open();

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanoipc_tcp->AccountWeight(['Account' => $account]);
}

echo 'Time TCP enc 3: ' . (microtime(true) - $t0) . PHP_EOL;

$nanoipc_tcp->close();


// # TCP encoding 4

$nanoipc_tcp = new php4nano\NanoIPC('tcp', ['localhost', 7077]);

$nanoipc_tcp->open();

$t0 = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    $nanoipc_tcp->AccountWeight(['account' => $account]);
}

echo 'Time TCP enc 4: ' . (microtime(true) - $t0) . PHP_EOL;

$nanoipc_tcp->close();
