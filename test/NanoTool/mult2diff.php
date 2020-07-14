<?php

require __DIR__ . '/../autoload.php';

use MikeRow\NanoPHP\NanoTool;

$difficulty = 'ffffffc000000000';
$multiplier = 0.125;

var_dump(NanoTool::mult2diff($difficulty, $multiplier));
