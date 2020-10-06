<?php 

require __DIR__ . '/autoload.php';

$bananows = new MikeRow\Bandano\BananoWS('ws', 'localhost', 7078);

$bananows->open();

$bananows->subscribe('confirmation', null, true);

$i = 0;
while ($i<5) {
    print_r($bananows->listen());
    $i++;
}

$bananows->unsubscribe('confirmation');

$bananows->close();
