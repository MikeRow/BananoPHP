<?php 

require __DIR__ . '/../autoload.php';

$account = 'ban_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$cycles = 10000;


// * Unix domain socket encoding 1

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(1);

$bananoipc_unix->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_unix->account_weight(['account' => $account]);
}

echo 'Time unix enc 1: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_unix->close();


// * Unix domain socket encoding 2

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(2);

$bananoipc_unix->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_unix->account_weight(['account' => $account]);
}

echo 'Time unix enc 2: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_unix->close();


// * Unix domain socket encoding 3

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(3);

$bananoipc_unix->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_unix->AccountWeight(['Account' => $account]);
}

echo 'Time unix enc 3: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_unix->close();


// * Unix domain socket encoding 4

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(4);

$bananoipc_unix->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_unix->AccountWeight(['account' => $account]);
}

echo 'Time unix enc 4: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_unix->close();


// * TCP encoding 1

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(1);

$bananoipc_tcp->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_tcp->account_weight(['account' => $account]);
}

echo 'Time TCP enc 1: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_tcp->close();


// * TCP encoding 2

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(2);

$bananoipc_tcp->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_tcp->account_weight(['account' => $account]);
}

echo 'Time TCP enc 2: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_tcp->close();


// * TCP encoding 3

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(3);

$bananoipc_tcp->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_tcp->AccountWeight(['Account' => $account]);
}

echo 'Time TCP enc 3: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_tcp->close();


// * TCP encoding 4

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(4);

$bananoipc_tcp->open();

$t0 = microtime(true);

for ($i = 0; $i < $cycles; $i++) {
    $bananoipc_tcp->AccountWeight(['account' => $account]);
}

echo 'Time TCP enc 4: ' . (microtime(true) - $t0) . PHP_EOL;

$bananoipc_tcp->close();
