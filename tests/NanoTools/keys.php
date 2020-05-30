<?php

    require_once __DIR__ . '/../../src/NanoTools.php';
    
    use php4nano\Nano\Tools as NanoTools;
    
    print_r( NanoTools::keys() );
    
    print_r( NanoTools::keys( true ) );