<?php 

require_once __DIR__ . '/autoload.php';

$pippincli = new php4nano\PippinCLI('/home/nano/.local/bin/pippin-cli');

$seed = 'D1352D4794EFBC666CBFC461AAB7EF4A989A11B5BA9E3C3A4473539155E06612';

$pippincli->wallet_create(['seed' => $seed]);

var_dump($pippincli);
