<?php

require_once __DIR__ . '/../autoload.php';

use php4nano\NanoTool;

$base_difficulty = 'ffffffc000000000';
$difficulty      = 'fffffe0000000000';

var_dump(NanoTool::diff2mult($base_difficulty, $difficulty));
