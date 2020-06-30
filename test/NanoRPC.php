<?php 

require_once __DIR__ . '/autoload.php';

$nanorpc = new php4nano\NanoRPC('localhost', 7076);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanorpc->account_balance(['account' => $account]);

var_dump($nanorpc);

$nanorpc2 = new php4nano\NanoRPC('localhost', 7076, 'api/v2');

$nanorpc2->setVersion(2);

$nanorpc2->AccountWeight(['account' => $account]);

var_dump($nanorpc2);
