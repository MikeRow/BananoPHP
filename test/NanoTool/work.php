<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

$hash       = 'A36B0B8CC84253E57C90E959755816EA51F00FA3497B8D2C665551FAECFBD0D0';
$difficulty = 'ffffffc000000000';

$i = 1;
$t0 = microtime(true);

while (true) {
    $work = NanoTool::work($hash, $difficulty);
    
    var_dump($work);
    
    echo 'Average: ' . (microtime(true) - $t0) / $i . PHP_EOL;
    
    echo 'Valid: ' . NanoTool::validWork($hash, $difficulty, $work);
    
    $i++;
}
