<?php 

require __DIR__ . '/autoload.php';

$account = 'ban_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// * Unix domain socket encoding 1

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(1);

$bananoipc_unix->open();

$bananoipc_unix->account_weight(['account' => $account]);

var_dump($bananoipc_unix);

$bananoipc_unix->close();


// * Unix domain socket encoding 2

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(2);

$bananoipc_unix->open();

$bananoipc_unix->account_weight(['account' => $account]);

var_dump($bananoipc_unix);

$bananoipc_unix->close();


// * Unix domain socket encoding 3

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(3);

$bananoipc_unix->open();

$bananoipc_unix->AccountWeight(['Account' => $account]);

var_dump($bananoipc_unix);

$bananoipc_unix->close();


// * Unix domain socket encoding 4

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(4);

$bananoipc_unix->open();

$bananoipc_unix->AccountWeight(['account' => $account]);

var_dump($bananoipc_unix);

$bananoipc_unix->close();


// * TCP encoding 1

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(1);

$bananoipc_tcp->open();

$bananoipc_tcp->account_weight(['account' => $account]);

var_dump($bananoipc_tcp);

$bananoipc_tcp->close();


// * TCP encoding 2

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(2);

$bananoipc_tcp->open();

$bananoipc_tcp->account_weight(['account' => $account]);

var_dump($bananoipc_tcp);

$bananoipc_tcp->close();


// * TCP encoding 3

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(3);

$bananoipc_tcp->open();

$bananoipc_tcp->AccountWeight(['Account' => $account]);

var_dump($bananoipc_tcp);

$bananoipc_tcp->close();


// * TCP encoding 4

$bananoipc_tcp = new MikeRow\BananoPHP\BananoIPC('tcp', ['localhost', 7077]);

$bananoipc_tcp->setBananoEncoding(4);

$bananoipc_tcp->open();

$bananoipc_tcp->AccountWeight(['account' => $account]);

var_dump($bananoipc_tcp);

$bananoipc_tcp->close();
