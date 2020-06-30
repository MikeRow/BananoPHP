<?php

if (file_exists(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} else {
    require __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../src/NanoBlock.php';
    require_once __DIR__ . '/../src/NanoCLI.php';
    require_once __DIR__ . '/../src/NanoRPC.php';
    require_once __DIR__ . '/../src/NanoRPCExt.php';
    require_once __DIR__ . '/../src/NanoTool.php';
}
