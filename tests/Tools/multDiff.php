<?php

require_once __DIR__ . '/../../src/Tools.php';

use php4nano\Tools as NanoTools;

$difficulty = 'ffffffc000000000';
$multiplier = 1.3;

echo NanoTools::multDiff($difficulty, $multiplier);
