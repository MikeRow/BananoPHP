<?php

require_once __DIR__ . '/../autoload.php';

use mikerow\php4nano\NanoTool;

$hash       = 'A36B0B8CC84253E57C90E959755816EA51F00FA3497B8D2C665551FAECFBD0D0';
$difficulty = 'ffffffc000000000';

$i = 1;
$t0 = time();

while (true) {
    $work = NanoTool::work($hash, $difficulty);
    var_dump($work);
    echo 'Average: ' . (time() - $t0) / $i . PHP_EOL;
    echo 'Valid: ' . NanoTool::validWork($hash, $difficulty, $work) . PHP_EOL;
    $i++;
}
