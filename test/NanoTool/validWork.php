<?php

require_once __DIR__ . '/../autoload.php';

use mikerow\php4nano\NanoTool;

$hash       = '584AD2078048C96373547627197ADCE066FB9F32722037A85C5D118649B21BB7';
$difficulty = 'ffffffc000000000';
$work       = '0c388975d5ea4495';

$t0 = microtime(true);

var_dump(NanoTool::validWork($hash, $difficulty, $work));

echo 'Time: ' . (microtime(true) - $t0) . PHP_EOL;
