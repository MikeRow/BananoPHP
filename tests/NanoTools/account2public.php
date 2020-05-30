<?php
    
    require_once __DIR__ . '/../src/NanoTools.php';
    
    use php4nano\Nano\Tools as NanoTools;
    
    echo NanoTools::account2public( 'nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx' );
    
    echo PHP_EOL;
    
    echo NanoTools::account2public( 'nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx', false );