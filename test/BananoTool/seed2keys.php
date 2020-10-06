<?php

require __DIR__ . '/../autoload.php';

use MikeRow\Bandano\BananoTool;

list($seed) = BananoTool::keys();

var_dump($seed);

var_dump(BananoTool::seed2keys($seed, 0));

var_dump(BananoTool::seed2keys($seed, 0, true));
