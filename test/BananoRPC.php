<?php 

require __DIR__ . '/autoload.php';

$account = 'ban_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// * API v1

$bananorpc = new MikeRow\Bandano\BananoRPC('http', 'localhost', 7076);

$bananorpc->setBananoApi(1);

$bananorpc->account_weight(['account' => $account]);

var_dump($bananorpc);


// * API v2

$bananorpc = new MikeRow\Bandano\BananoRPC('http', 'localhost', 7076, 'api/v2');

$bananorpc->setBananoApi(2);

$bananorpc->AccountWeight(['account' => $account]);

var_dump($bananorpc);
