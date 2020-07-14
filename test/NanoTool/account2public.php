<?php

require __DIR__ . '/../autoload.php';

use MikeRow\NanoPHP\NanoTool;

$t0 = microtime(true);
var_dump(NanoTool::account2public('nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx'));

echo 'Time public_key: ' . (microtime(true) - $t0) . PHP_EOL;

$t0 = microtime(true);
var_dump(NanoTool::account2public('nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx', false));

echo 'Time valid_only: ' . (microtime(true) - $t0) . PHP_EOL;
