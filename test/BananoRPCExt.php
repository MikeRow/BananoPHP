<?php 

require __DIR__ . '/autoload.php';

$bananorpcext = new MikeRow\BananoPHP\BananoRPCExt('http', 'localhost', 7076);

$account = 'ban_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';

$bananorpcext->account_weight(['account' => $account]);

var_dump($bananorpcext);
