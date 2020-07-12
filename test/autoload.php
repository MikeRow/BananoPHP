<?php

if (file_exists(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} else {
    require __DIR__ . '/../vendor/autoload.php';
    require __DIR__ . '/../lib/Salt/autoload.php';
    require __DIR__ . '/../lib/flatbuffers/autoload.php';
    require __DIR__ . '/../lib/util/base.php';
    require __DIR__ . '/../lib/util/bin.php';
    require __DIR__ . '/../lib/util/Uint.php';
    require __DIR__ . '/../src/NanoBlock.php';
    require __DIR__ . '/../src/NanoCLI.php';
    require __DIR__ . '/../src/NanoIPC.php';
    require __DIR__ . '/../src/NanoRPC.php';
    require __DIR__ . '/../src/NanoRPCExt.php';
    require __DIR__ . '/../src/NanoTool.php';
    require __DIR__ . '/../src/NanoWS.php';
    require __DIR__ . '/../src/PippinCLI.php';
}
