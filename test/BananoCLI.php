<?php 

require __DIR__ . '/autoload.php';

$bananocli = new MikeRow\BananoPHP\BananoCLI('/home/banano/banano_node');

$account = 'ban_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$bananocli->account_key(['account' => $account]);

var_dump($bananocli);
