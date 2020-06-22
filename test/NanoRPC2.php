<?php 

require_once __DIR__ . '/../autoload.php';

$nanorpc2 = new php4nano\NanoRPC2('localhost', '7076', 'api/v2');

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanorpc2->AccountWeight(['account' => $account]);

var_dump($nanorpc2);
