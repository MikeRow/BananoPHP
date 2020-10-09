<?php

require __DIR__ . '/../autoload.php';

use MikeRow\BananoPHP\BananoTool;

$base_difficulty = 'ffffffc000000000';
$difficulty      = 'fffffe0000000000';

var_dump(BananoTool::diff2mult($base_difficulty, $difficulty));
