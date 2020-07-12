<?php 

require __DIR__ . '/autoload.php';

$nanorpcext = new mikerow\php4nano\NanoRPCExt('http', 'localhost', 7076);

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanorpcext->account_weight(['account' => $account]);

var_dump($nanorpcext);
