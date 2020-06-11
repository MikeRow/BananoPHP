<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

$t0 = microtime(true);

var_dump(NanoTool::account2public('nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx'));

$t1 = microtime(true);
echo ($t1 - $t0) . PHP_EOL;

var_dump(NanoTool::account2public('nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx', false));

$t2 = microtime(true);
echo ($t2 - $t1) . PHP_EOL;
