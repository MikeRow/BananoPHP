<?php

    require_once __DIR__ . '/../../src/Tools.php';
    
    use php4nano\Tools as NanoTools;
    
    echo NanoTools::den2den( '5', 'NANO', 'nano' );