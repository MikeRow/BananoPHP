<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

list($seed) = NanoTool::keys();

var_dump(NanoTool::seed2keys($seed, 0));

var_dump(NanoTool::seed2keys($seed, 0, true));
