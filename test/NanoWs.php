<?php 

require __DIR__ . '/autoload.php';

$nanows = new MikeRow\NanoPHP\NanoWs('ws', 'localhost', 7078);

$nanows->open();

$nanows->subscribe('confirmation', null, true);

$i = 0;
while ($i<5) {
    print_r($nanows->listen());
    $i++;
}

$nanows->unsubscribe('confirmation');

$nanows->close();
