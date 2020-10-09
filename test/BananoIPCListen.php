<?php 

require __DIR__ . '/autoload.php';

$account = 'ban_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// * Unix domain socket listening

$bananoipc_unix = new MikeRow\BananoPHP\BananoIPC('unix', ['/tmp/nano']);

$bananoipc_unix->setBananoEncoding(3);

$bananoipc_unix->setListen(true);

$bananoipc_unix->open();

$args = [
    'ServiceName' => 'TopicConfirmation'
];

$bananoipc_unix->ServiceRegister($args);

$i = 0;
while ($i<5) {
    print_r($bananoipc_unix->listen());
    $i++;
}

$bananoipc_unix->close();
