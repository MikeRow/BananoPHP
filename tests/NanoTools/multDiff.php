<?php

    require_once __DIR__ . '/../../src/NanoTools.php';
    
    use php4nano\Nano\Tools as NanoTools;
    
    $difficulty = 'ffffffc000000000';
    $multiplier = 1.3;
    
    echo NanoTools::multDiff( $difficulty, $multiplier );