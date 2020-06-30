<?php 

require_once __DIR__ . '/autoload.php';

$nanows = new php4nano\NanoWS('localhost', 7078);

$nanows->subscribe('confirmation');
//$nanows->keepalive();

while (true) {
    print_r($nanows->listen());
}

$nanows->unsubscribe('confirmation');
