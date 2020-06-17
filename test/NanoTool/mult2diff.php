<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

$difficulty = 'ffffffc000000000';
$multiplier = 0.125;

var_dump(NanoTool::mult2diff($difficulty, $multiplier));
