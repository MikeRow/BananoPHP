<?php 

require_once __DIR__ . '/autoload.php';

$nanows = new php4nano\NanoWS('localhost', 7078);

$nanows->open();

$nanows->subscribe('confirmation', null, true);

$i = 0;
while ($i<10) {
    print_r($nanows->listen());
    $i++;
}

$nanows->unsubscribe('confirmation');

$nanows->close();
