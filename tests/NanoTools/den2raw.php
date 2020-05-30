<?php

    require_once __DIR__ . '/../../src/NanoTools.php';
    
    use php4nano\Nano\Tools as NanoTools;
    
    echo NanoTools::den2raw( '5', 'NANO' );