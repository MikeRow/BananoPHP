<?php

require_once __DIR__ . '/../../lib/NanoTools.php';

use php4nano\NanoTools as NanoTools;

$difficulty = 'ffffffc000000000';
$multiplier = 1.3;

var_dump(NanoTools::multDiff($difficulty, $multiplier));
