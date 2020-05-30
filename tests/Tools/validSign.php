<?php

    require_once __DIR__ . '/../../src/Tools.php';
    
    use php4nano\Tools as NanoTools;

    $msg        = '36E778DEDF4094AD9424C28F3198150328FD33B9A08BEA88C177A11B898E156B';
    $sign       = 'DBC3C967C01AC502FDC135EA42DDEB3072C94A0B7B0E0DA1DA5C6A73C8495F1802D643CBA5E8C97001E7FA5D3BEE267E2AC2C2754B262134F6135DDA229C3A00';
    $account    = 'nano_3pgkm4fcxt3ks1m5kapfuuzjjqi16a791y1dgbsbqhr5ojo4j5qokp1pczg1';
    
    var_dump(NanoTools::validSign($msg, $sign, $account));
