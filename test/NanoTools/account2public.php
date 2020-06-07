<?php

require_once __DIR__ . '/../../lib/NanoTool.php';

use php4nano\NanoTool as NanoTool;

echo NanoTool::account2public('nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx');

echo PHP_EOL;

var_dump(NanoTool::account2public('nano_3ieu9rjq8uyd3h1taykfb3s14g5p6mnu73hep4iox8w91hew3147eejgogxx', false));
