<?php 

require_once __DIR__ . '/../../src/RPC.php';

$nanorpc = new php4nano\RPC();

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanorpc->account_balance(['account' => $account]);

print_r($nanorpc);