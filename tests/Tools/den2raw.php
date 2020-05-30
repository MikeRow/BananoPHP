<?php

    require_once __DIR__ . '/../../src/Tools.php';
    
    use php4nano\Tools as NanoTools;

    echo NanoTools::den2raw('5', 'NANO');
