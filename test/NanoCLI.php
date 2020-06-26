<?php 

require_once __DIR__ . '/autoload.php';

$nanocli = new php4nano\NanoCLI('/home/nano/nano_node');

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$nanocli->account_key(['account' => $account]);

var_dump($nanocli);
