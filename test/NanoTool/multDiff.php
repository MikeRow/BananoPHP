<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

$difficulty = 'ffffffc000000000';
$multiplier = 1.3;

var_dump(NanoTool::multDiff($difficulty, $multiplier));
