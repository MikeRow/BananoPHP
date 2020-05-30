<?php

    require_once __DIR__ . '/../../src/NanoTools.php';
    
    use php4nano\Nano\Tools as NanoTools;
    
    $mnem =
    [
        'turkey',
        'fever',
        'wish',
        'tray',
        'remind',
        'abandon',
        'announce',
        'skin',
        'input',
        'permit',
        'mobile',
        'exclude',
        'ghost',
        'album',
        'floor',
        'utility',
        'attack',
        'oil',
        'payment',
        'stumble',
        'noise',
        'orbit',
        'grain',
        'dash'
    ];
    
    echo NanoTools::mnem2mseed( $mnem );