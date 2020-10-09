<?php

require __DIR__ . '/../autoload.php';

use MikeRow\BananoPHP\BananoTool;

$difficulty = 'ffffffc000000000';
$multiplier = 0.125;

var_dump(BananoTool::mult2diff($difficulty, $multiplier));
