<?php 

require_once __DIR__ . '/autoload.php';

$nanorpc = new php4nano\NanoRPC();

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanorpc->account_balance(['account' => $account]);

var_dump($nanorpc);
