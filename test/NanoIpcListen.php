<?php 

require __DIR__ . '/autoload.php';

$account = 'nano_3dyo9e7wkf8kuykghbjdt78njux3yudhdrhtwaymc8fsmxhxpt1h48zffbse';


// * Unix domain socket listening

$nanoipc_unix = new MikeRow\NanoPHP\NanoIpc('unix', ['/tmp/nano']);

$nanoipc_unix->setNanoEncoding(4);

$nanoipc_unix->setListening(true);

$nanoipc_unix->open();

$nanoipc_unix->ServiceRegister($args);

$i = 0;
while ($i<5) {
    print_r($nanoipc_unix->listen());
    $i++;
}

$nanoipc_unix->close();
