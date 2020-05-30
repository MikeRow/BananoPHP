<?php

    require_once __DIR__ . '/../src/NanoTools.php';
    
    use php4nano\Nano\Tools as NanoTools;
    
    echo NanoTools::den2den( '5', 'NANO', 'nano' );