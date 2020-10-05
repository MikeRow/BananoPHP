<?php 

require __DIR__ . '/autoload.php';

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// * API v1

$nanorpc = new MikeRow\Bandano\NanoRPC('http', 'localhost', 7076);

$nanorpc->setNanoApi(1);

$nanorpc->account_weight(['account' => $account]);

var_dump($nanorpc);


// * API v2

$nanorpc = new MikeRow\Bandano\NanoRPC('http', 'localhost', 7076, 'api/v2');

$nanorpc->setNanoApi(2);

$nanorpc->AccountWeight(['account' => $account]);

var_dump($nanorpc);
